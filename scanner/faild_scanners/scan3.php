<?php
// secu_scan_windows.php
// Windows-friendly SecuScan (PHP 8+). Synchronous runs with timeout and auto-detect of tools.
// Save to web root and create a writable "scans" folder next to this file.

ini_set('display_errors',1);
ini_set('max_execution_time', 0);
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'demo_user';
}

$BASE_DIR = __DIR__;
$SCANS_DIR = $BASE_DIR . DIRECTORY_SEPARATOR . 'scans';
if (!is_dir($SCANS_DIR)) mkdir($SCANS_DIR, 0770, true);

function safe_target(string $t): string {
    $t = trim($t);
    $t = preg_replace('#^https?://#i','',$t);
    $t = rtrim($t, "/ ");
    return $t;
}

/*
 * Small-ish tools list. If a tool binary is not in PATH, it will be hidden automatically.
 * If you want to point to a specific Windows path, replace the command token with full path to exe.
 */
$TOOLS = [
    'nmap_basic' => ['label'=>'Nmap: Basic (fast)', 'cmd'=>'nmap -Pn -sS -T4 %TARGET%'],
    'nmap_vuln'  => ['label'=>'Nmap: Vulnerability Scripts', 'cmd'=>'nmap --script vuln -sV %TARGET%'],
    'curl_headers' => ['label'=>'HTTP Headers (curl -I)', 'cmd'=>'curl -I --max-time 20 %TARGET%'],
    'sqlmap' => ['label'=>'sqlmap (web SQLi scan)', 'cmd'=>'sqlmap -u %TARGET% --batch --threads=1'],
    'nikto' => ['label'=>'Nikto (web vuln scan)', 'cmd'=>'nikto -h %TARGET%'],
    'sslscan' => ['label'=>'sslscan (TLS)', 'cmd'=>'sslscan %TARGET%'],
    'gobuster' => ['label'=>'Gobuster (dir bruteforce)', 'cmd'=>'gobuster dir -u %TARGET% -w C:\\wordlists\\common.txt -t 20'],
    // Add other tools here; use full windows path if needed
];

/**
 * Try to detect whether a command exists.
 * On Windows use "where", on Linux/WSL use "which".
 */
function command_exists(string $binary): bool {
    // If binary contains path separators, check file existence
    if (strpos($binary, DIRECTORY_SEPARATOR) !== false || strpos($binary, '/') !== false || strpos($binary, '\\') !== false) {
        return file_exists($binary);
    }

    // Try 'where' (Windows)
    $where = @trim(shell_exec("where " . escapeshellarg($binary) . " 2>NUL"));
    if ($where !== '') return true;

    // Try 'which' (Linux/WSL)
    $which = @trim(shell_exec("which " . escapeshellarg($binary) . " 2>/dev/null"));
    if ($which !== '') return true;

    // Try 'command -v'
    $cmdv = @trim(shell_exec("command -v " . escapeshellarg($binary) . " 2>/dev/null"));
    if ($cmdv !== '') return true;

    return false;
}

/**
 * Extract executable token from a command string.
 * Example: "nmap -Pn" => "nmap", "C:\Tools\nmap.exe -..." => "C:\Tools\nmap.exe"
 */
function first_command_token(string $cmd): string {
    $cmd = trim($cmd);
    // Remove initial "timeout" wrappers sometimes used in Linux commands
    $parts = preg_split('/\s+/', $cmd);
    if (strtolower($parts[0]) === 'timeout' && isset($parts[1])) {
        array_shift($parts); // remove timeout
    }
    return $parts[0] ?? '';
}

// Auto-disable tools not available on system
foreach (array_keys($TOOLS) as $k) {
    $token = first_command_token($TOOLS[$k]['cmd']);
    // tokens can include paths, quotes, etc.
    $token = trim($token, "\"'");
    if ($token === '') { unset($TOOLS[$k]); continue; }
    if (!command_exists($token)) {
        unset($TOOLS[$k]);
    }
}

/**
 * Run command with timeout using proc_open and non-blocking streams.
 * Returns array: [ 'exit' => int|null, 'output' => string, 'timed_out' => bool ]
 */
function run_command(string $cmd, int $timeout = 30): array {
    $descriptorSpec = [
        0 => ['pipe', 'r'], // stdin
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w']  // stderr
    ];

    $process = @proc_open($cmd, $descriptorSpec, $pipes, null, null);
    if (!is_resource($process)) {
        return ['exit' => null, 'output' => "Failed to start process (proc_open returned false). Command: $cmd", 'timed_out' => false];
    }

    // Close stdin
    fclose($pipes[0]);

    // Set non-blocking
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $start = time();
    $output = '';
    $timed_out = false;

    while (true) {
        $read = [$pipes[1], $pipes[2]];
        $write = null;
        $except = null;
        // Wait up to 0.2s for data
        $num = stream_select($read, $write, $except, 0, 200000);

        if ($num === false) break;

        foreach ($read as $r) {
            $chunk = stream_get_contents($r);
            if ($chunk !== false && $chunk !== '') {
                $output .= $chunk;
            }
        }

        $status = proc_get_status($process);
        if (!$status['running']) {
            // Collect remaining
            $output .= stream_get_contents($pipes[1]);
            $output .= stream_get_contents($pipes[2]);
            break;
        }

        if ((time() - $start) > $timeout) {
            // timeout reached
            $timed_out = true;
            // attempt terminate gracefully
            proc_terminate($process);
            // give it a second then force close
            sleep(1);
            $status = proc_get_status($process);
            if ($status['running']) {
                proc_close($process); // force close
            }
            break;
        }

        // tiny sleep to prevent busy loop
        usleep(100000);
    }

    // close pipes
    foreach ($pipes as $p) {
        @fclose($p);
    }

    $exitCode = proc_get_status($process)['exitcode'] ?? null;
    // Final close (ensure process resource freed)
    @proc_close($process);

    return ['exit' => $exitCode, 'output' => $output, 'timed_out' => $timed_out];
}

// Handlers
$action = $_GET['action'] ?? '';

if ($action === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $raw_target = $_POST['target'] ?? '';
    $tool = $_POST['tool'] ?? '';
    $target = safe_target($raw_target);

    if (!isset($TOOLS[$tool])) {
        echo json_encode(['ok'=>false,'msg'=>'Tool not supported or not installed on server.']); exit;
    }

    $template = $TOOLS[$tool]['cmd'];
    $escaped = escapeshellarg($target);
    $cmd = str_replace('%TARGET%', $escaped, $template);

    // Windows: often you want to run via cmd /C to ensure PATH resolution; but proc_open can run directly
    // For safety if command contains special characters, prefer to run via the shell:
    if (stripos(PHP_OS, 'WIN') === 0) {
        // Use cmd /C to run the full command string
        $cmdToRun = 'cmd /C ' . $cmd;
    } else {
        $cmdToRun = $cmd;
    }

    $id = bin2hex(random_bytes(8));
    $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.txt';

    // Timeout (seconds) - tune per-tool
    $toolTimeouts = [
        'nmap_basic' => 25,
        'nmap_vuln'  => 120,
        'curl_headers' => 10,
        'sqlmap' => 60,
        'nikto' => 60,
        'sslscan' => 30,
        'gobuster' => 90,
    ];
    $timeout = $toolTimeouts[$tool] ?? 45;

    $res = run_command($cmdToRun, $timeout);

    $summary = "CMD: $cmdToRun\nEXIT: " . var_export($res['exit'], true) . "\nTIMED_OUT: " . ($res['timed_out'] ? 'yes' : 'no') . "\n\n---OUTPUT---\n\n";
    $fullOut = $summary . $res['output'];

    file_put_contents($outFile, $fullOut);

    $meta = [
        'id'=>$id,
        'user'=>$_SESSION['username'],
        'tool'=>$tool,
        'target'=>$target,
        'cmd'=>$cmdToRun,
        'outfile'=>basename($outFile),
        'started'=>time(),
        'timed_out'=>$res['timed_out']
    ];
    file_put_contents($SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.json', json_encode($meta));

    echo json_encode(['ok'=>true,'id'=>$id,'outfile'=>basename($outFile),'timed_out'=>$res['timed_out'],'preview'=>substr($res['output'],0,1200)]);
    exit;
}

if ($action === 'status') {
    header('Content-Type: application/json');
    $id = $_GET['id'] ?? '';
    $metaFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.json';
    $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.txt';
    if (!file_exists($metaFile)) { echo json_encode(['ok'=>false,'msg'=>'Job not found']); exit; }
    $meta = json_decode(file_get_contents($metaFile), true);
    $output = file_exists($outFile) ? substr(file_get_contents($outFile),0,200000) : '';
    echo json_encode(['ok'=>true,'meta'=>$meta,'running'=>false,'output'=>$output]);
    exit;
}

if ($action === 'download') {
    $id = $_GET['id'] ?? '';
    $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.txt';
    if (!file_exists($outFile)) { http_response_code(404); echo 'Not found'; exit; }
    header('Content-Type','text/plain');
    header('Content-Disposition','attachment; filename="scan_' . $id . '.txt"');
    readfile($outFile);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SecuScan (Windows)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> body{background:#f6f8fa} pre{background:#0b0b0b;color:#b7f0b7;padding:12px;border-radius:6px;max-height:480px;overflow:auto;} </style>
</head>
<body>
<nav class="navbar navbar-light bg-white shadow-sm"><div class="container"><a class="navbar-brand" href="#">SecuScan</a><div>User: <?php echo htmlspecialchars($_SESSION['username']); ?></div></div></nav>
<div class="container py-4">
  <div class="row">
    <div class="col-md-5">
      <div class="card mb-3"><div class="card-body">
        <h5>New Scan</h5>
        <form id="scanForm">
          <div class="mb-2"><label>Target</label><input id="target" name="target" class="form-control" placeholder="example.com or 1.2.3.4" required></div>
          <div class="mb-2"><label>Tool</label>
            <select id="tool" name="tool" class="form-select">
              <?php foreach($TOOLS as $k=>$t): ?>
                <option value="<?php echo $k; ?>"><?php echo htmlspecialchars($t['label']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2 form-check"><input id="agreeDanger" class="form-check-input" type="checkbox"><label class="form-check-label" for="agreeDanger">I have authorization to scan this target.</label></div>
          <button class="btn btn-primary" type="submit">Run</button>
        </form>
      </div></div>

      <div class="card"><div class="card-body">
        <h6>Quick tips</h6>
        <ul>
          <li>Put tools' executables in your PATH (or adjust commands to full paths).</li>
          <li>Create writable "scans" folder next to this file.</li>
          <li>This runs tools synchronously and enforces per-tool timeouts.</li>
          <li>Only scan targets you have explicit permission to scan.</li>
        </ul>
      </div></div>
    </div>

    <div class="col-md-7">
      <div class="card mb-3"><div class="card-body">
        <h5>Recent Scans</h5>
        <div id="jobsList">Loading...</div>
      </div></div>

      <div class="card"><div class="card-body">
        <h5>Scan Output</h5>
        <div id="outputArea"><em>No scan yet.</em></div>
      </div></div>
    </div>
  </div>
</div>

<script>
async function loadJobs(){
  const res = await fetch(location.pathname + '?action=list_jobs');
  try {
    const js = await res.json();
    const jobs = js.jobs || [];
    const el = document.getElementById('jobsList');
    if (jobs.length === 0) { el.innerHTML = '<div class="text-muted">No scans yet.</div>'; return; }
    let html = '<div class="list-group">';
    jobs.forEach(j => {
      html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectJob('${j.id}')">
        <div class="d-flex w-100 justify-content-between"><h6 class="mb-1">${j.tool} — ${j.target}</h6></div>
        <p class="mb-1 small text-monospace">${j.started_human} — <a href='?action=download&id=${j.id}'>download</a></p>
      </a>`;
    });
    html += '</div>';
    el.innerHTML = html;
  } catch(e) {
    document.getElementById('jobsList').innerHTML = '<div class="text-danger">Failed to load jobs</div>';
  }
}

document.getElementById('scanForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const target = document.getElementById('target').value.trim();
  const tool = document.getElementById('tool').value;
  const agreed = document.getElementById('agreeDanger').checked;
  const brute = ['hydra_ssh','medusa_ssh'];
  if (brute.includes(tool) && !agreed) { alert('You must confirm authorization'); return; }
  const fd = new FormData();
  fd.append('target', target);
  fd.append('tool', tool);
  const res = await fetch('?action=run', { method:'POST', body: fd });
  const js = await res.json();
  if (!js.ok) { alert('Error: ' + (js.msg||'unknown')); return; }
  // show preview and download
  document.getElementById('outputArea').innerHTML = `<pre>${(js.preview||'No output')}</pre><p><a href='?action=download&id=${js.id}' class='btn btn-sm btn-outline-secondary'>Download full</a></p>`;
  loadJobs();
});

async function selectJob(id){
  document.getElementById('outputArea').innerHTML = '<em>Loading...</em>';
  const res = await fetch('?action=status&id=' + encodeURIComponent(id));
  const js = await res.json();
  if (!js.ok) { document.getElementById('outputArea').innerHTML = '<div class="text-danger">Job not found</div>'; return; }
  document.getElementById('outputArea').innerHTML = `<pre>${js.output||'No output'}</pre>`;
}

setInterval(loadJobs, 5000);
loadJobs();
</script>
</body>
</html>

<?php
// job listing for front-end
if (isset($_GET['action']) && $_GET['action'] === 'list_jobs') {
    header('Content-Type: application/json');
    $files = glob($SCANS_DIR . DIRECTORY_SEPARATOR . '*.json');
    $jobs = [];
    foreach ($files as $f) {
        $m = json_decode(file_get_contents($f), true);
        if (!$m) continue;
        $jobs[] = [
            'id'=>$m['id'],
            'tool'=>$m['tool'],
            'target'=>$m['target'],
            'started'=>$m['started'],
            'started_human'=>date('Y-m-d H:i:s', $m['started'])
        ];
    }
    usort($jobs, function($a,$b){ return $b['started'] - $a['started']; });
    echo json_encode(['ok'=>true,'jobs'=>$jobs]);
    exit;
}
?>
