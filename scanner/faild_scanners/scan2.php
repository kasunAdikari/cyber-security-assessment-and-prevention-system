<?php
// secu_scan_single_file.php
// Single-file SecuScan hub. Requires: PHP 8+, shell tools installed by user.
// Create folder 'scans' writable by web server: chmod 770 scans
// Use at your own risk. This file runs shell commands on the host - ensure only trusted users can access.

ini_set('display_errors',1);
ini_set('max_execution_time', 0);
session_start();

// simple auth demo (replace with your real auth system)
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'demo_user';
}

$BASE_DIR = __DIR__;
$SCANS_DIR = $BASE_DIR . DIRECTORY_SEPARATOR . 'scans';
if (!is_dir($SCANS_DIR)) mkdir($SCANS_DIR, 0770, true);

function safe_target($t) {
    $t = trim($t);
    $t = preg_replace('#^https?://#i','',$t);
    $t = rtrim($t, "/ ");
    return $t;
}

// whitelist of tools -> command template. %TARGET% will be replaced with escaped target.
$TOOLS = [
    'nmap_basic' => ['label'=>'Nmap: Basic (fast)', 'cmd'=>'nmap -Pn -sS -T4 %TARGET%'],
    'nmap_advanced' => ['label'=>'Nmap: Advanced (service/version, scripts)', 'cmd'=>'nmap -A -sV -sC -p- %TARGET%'],
    'nmap_vuln' => ['label'=>'Nmap: Vulnerability Scripts', 'cmd'=>'nmap --script vuln -sV %TARGET%'],
    'masscan' => ['label'=>'Masscan (fast port discovery)', 'cmd'=>'masscan %TARGET% -p1-65535 --rate=10000'],
    'nikto' => ['label'=>'Nikto (web vuln scan)', 'cmd'=>'nikto -h %TARGET%'],
    'wpscan' => ['label'=>'WPScan (WordPress)', 'cmd'=>'wpscan --url %TARGET% --no-update'],
    'sqlmap' => ['label'=>'sqlmap (web SQLi scan)', 'cmd'=>'sqlmap -u %TARGET% --batch --threads=3'],
    'gobuster' => ['label'=>'Gobuster (directory brute)', 'cmd'=>'gobuster dir -u %TARGET% -w /usr/share/wordlists/dirb/common.txt -t 20'],
    'dirb' => ['label'=>'Dirb (directory brute)', 'cmd'=>'dirb %TARGET% /usr/share/wordlists/dirb/common.txt'],
    'testssl' => ['label'=>'testssl.sh (TLS checks)', 'cmd'=>'testssl.sh --openssl=openssl %TARGET%'],
    'sslyze' => ['label'=>'SSLyze (TLS audit)', 'cmd'=>'sslyze --regular %TARGET%'],
    'sublist3r' => ['label'=>'Subdomain Enumeration (Sublist3r)', 'cmd'=>'python3 sublist3r.py -d %TARGET% -o -'],
    'amass' => ['label'=>'Amass (recon)', 'cmd'=>'amass enum -d %TARGET%'],
    'theharvester' => ['label'=>'TheHarvester (OSINT)', 'cmd'=>'theHarvester -d %TARGET% -b all -l 200'],
    'shodan' => ['label'=>'Shodan (requires API key set in environment variable SHODAN_API_KEY)', 'cmd'=>'shodan host %TARGET%'],
    'dnsenum' => ['label'=>'dnsenum (DNS)', 'cmd'=>'dnsenum %TARGET%'],
    'traceroute' => ['label'=>'Traceroute', 'cmd'=>'traceroute %TARGET%'],
    'curl_headers' => ['label'=>'HTTP Headers (curl -I)', 'cmd'=>'curl -I --max-time 20 %TARGET%'],
    'hydra_ssh' => ['label'=>'Hydra (SSH brute) *use responsibly*', 'cmd'=>'hydra -L /usr/share/wordlists/usernames.txt -P /usr/share/wordlists/rockyou.txt ssh://%TARGET% -t 4 -f'],
    'medusa_ssh' => ['label'=>'Medusa (SSH brute) *use responsibly*', 'cmd'=>'medusa -h %TARGET% -u root -P /usr/share/wordlists/rockyou.txt -M ssh'],
    'sslscan' => ['label'=>'sslscan (TLS)', 'cmd'=>'sslscan %TARGET%'],
];

// Endpoint handlers
$action = $_GET['action'] ?? '';
if ($action === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $raw_target = $_POST['target'] ?? '';
    $tool = $_POST['tool'] ?? '';
    $target = safe_target($raw_target);

    if (!isset($TOOLS[$tool])) {
        echo json_encode(['ok'=>false,'msg'=>'Tool not supported']); exit;
    }

    $template = $TOOLS[$tool]['cmd'];
    $escaped = escapeshellarg($target);
    $cmd = str_replace('%TARGET%', $escaped, $template);

    $id = bin2hex(random_bytes(8));
    $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.txt';

    $bg = "nohup $cmd > " . escapeshellarg($outFile) . " 2>&1 & echo $!";
    $pid = trim(shell_exec($bg));

    $meta = [
        'id'=>$id,
        'user'=>$_SESSION['username'],
        'tool'=>$tool,
        'target'=>$target,
        'cmd'=>$cmd,
        'pid'=>$pid,
        'outfile'=>basename($outFile),
        'started'=>time()
    ];
    file_put_contents($SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.json', json_encode($meta));

    echo json_encode(['ok'=>true,'id'=>$id,'pid'=>$pid,'outfile'=>basename($outFile)]);
    exit;
}

if ($action === 'status') {
    header('Content-Type: application/json');
    $id = $_GET['id'] ?? '';
    $metaFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.json';
    $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $id . '.txt';
    if (!file_exists($metaFile)) { echo json_encode(['ok'=>false,'msg'=>'Job not found']); exit; }
    $meta = json_decode(file_get_contents($metaFile), true);

    $running = false;
    if (!empty($meta['pid'])) {
        $check = shell_exec('ps -p ' . escapeshellarg($meta['pid']) . ' 2>/dev/null | wc -l');
        if (intval(trim($check)) > 1) $running = true;
    }

    $output = '';
    if (file_exists($outFile)) $output = substr(file_get_contents($outFile), 0, 200000);

    echo json_encode(['ok'=>true,'meta'=>$meta,'running'=>$running,'output'=>$output]);
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

// FRONTEND HTML below
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SecuScan Hub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f6f8fa }
    pre { background:#0b0b0b; color:#b7f0b7; padding:12px; border-radius:6px; max-height:480px; overflow:auto; }
    .tool-label { font-size:0.95rem }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="#">SecuScan</a>
    <div class="d-flex align-items-center">User: <?php echo htmlspecialchars($_SESSION['username']); ?></div>
  </div>
</nav>

<div class="container py-4">
  <div class="row">
    <div class="col-md-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">New Scan</h5>
          <form id="scanForm">
            <div class="mb-3">
              <label class="form-label">Target (IP / domain / url)</label>
              <input class="form-control" name="target" id="target" placeholder="example.com or 1.2.3.4" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tool</label>
              <select class="form-select" name="tool" id="tool">
<?php foreach($TOOLS as $key=>$t): ?>
  <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($t['label']); ?></option>
<?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="agreeDanger">
              <label class="form-check-label" for="agreeDanger">I understand some tools are intrusive and I have authorization to scan this target.</label>
            </div>
            <button class="btn btn-primary" id="startBtn" type="submit">Start Scan (background)</button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h6>Quick tips</h6>
          <ul>
            <li>Install tools on the server: nmap, masscan, nikto, wpscan, sqlmap, gobuster, testssl.sh, sslyze, sublist3r, amass, theHarvester, shodan, dnsenum, hydra, medusa, sslscan.</li>
            <li>Create a writable "scans" folder next to this file.</li>
            <li>Background scans run with <code>nohup</code> and results saved to <code>scans/*.txt</code>.</li>
            <li>For potentially destructive actions (brute force), check the authorization checkbox.</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Active / Recent Scans</h5>
          <div id="jobsList">Loading...</div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Scan Output</h5>
          <div id="outputArea"><em>No scan selected.</em></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
async function fetchJobs() {
  const res = await fetch(location.pathname + '?action=list_jobs');
  const js = await res.json();
  const jobs = js.jobs || [];
  const el = document.getElementById('jobsList');
  if (jobs.length === 0) { el.innerHTML = '<div class="text-muted">No scans yet.</div>'; return; }
  let html = '<div class="list-group">';
  jobs.forEach(j => {
    html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectJob('${j.id}')">
      <div class="d-flex w-100 justify-content-between">
        <h6 class="mb-1">${j.tool} — ${j.target}</h6>
        <small>${j.running?'<span class="badge bg-warning">running</span>':'<span class="badge bg-secondary">done</span>'}</small>
      </div>
      <p class="mb-1 small text-monospace">${j.started_human} — pid:${j.pid || '-'} — <a href='?action=download&id=${j.id}'>download</a></p>
    </a>`;
  });
  html += '</div>';
  el.innerHTML = html;
}

async function startScan(ev) {
  ev.preventDefault();
  const target = document.getElementById('target').value.trim();
  const tool = document.getElementById('tool').value;
  const agreed = document.getElementById('agreeDanger').checked;
  const bruteTools = ['hydra_ssh','medusa_ssh'];
  if (bruteTools.includes(tool) && !agreed) { alert('You must check the authorization box for brute-force tools.'); return; }
  const fd = new FormData();
  fd.append('target', target);
  fd.append('tool', tool);
  document.getElementById('startBtn').disabled = true;
  const res = await fetch('?action=run', { method:'POST', body: fd });
  const js = await res.json();
  if (!js.ok) { alert('Failed: ' + (js.msg||'unknown')); document.getElementById('startBtn').disabled=false; return; }
  alert('Job started: ' + js.id);
  document.getElementById('startBtn').disabled=false;
  loadJobs();
}

async function loadJobs() {
  const res = await fetch(location.pathname + '?action=list_jobs');
  const js = await res.json();
  const jobs = js.jobs || [];
  const el = document.getElementById('jobsList');
  if (jobs.length === 0) { el.innerHTML = '<div class="text-muted">No scans yet.</div>'; return; }
  let html = '<div class="list-group">';
  jobs.forEach(j => {
    html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectJob('${j.id}')">
      <div class="d-flex w-100 justify-content-between">
        <h6 class="mb-1">${j.tool} — ${j.target}</h6>
        <small>${j.running?'<span class="badge bg-warning">running</span>':'<span class="badge bg-secondary">done</span>'}</small>
      </div>
      <p class="mb-1 small text-monospace">${j.started_human} — pid:${j.pid || '-'} — <a href='?action=download&id=${j.id}'>download</a></p>
    </a>`;
  });
  html += '</div>';
  el.innerHTML = html;
}

async function selectJob(id) {
  document.getElementById('outputArea').innerHTML = '<em>Loading...</em>';
  const res = await fetch('?action=status&id=' + encodeURIComponent(id));
  const js = await res.json();
  if (!js.ok) { document.getElementById('outputArea').innerHTML = '<div class="text-danger">Job not found</div>'; return; }
  const out = js.output || 'No output yet.';
  document.getElementById('outputArea').innerHTML = `<pre>${escapeHtml(out)}</pre>`;
}

function escapeHtml(s){ return s.replace(/[&"'<>]/g, function(c){ return {'&':'&amp;','"':'&quot;',"'":"&#39;","<":"&lt;",">":"&gt;"}[c]; }); }

document.getElementById('scanForm').addEventListener('submit', startScan);
setInterval(loadJobs, 5000);
loadJobs();
</script>

</body>
</html>

<?php
if (isset($_GET['action']) && $_GET['action']==='list_jobs') {
    header('Content-Type: application/json');
    $files = glob($SCANS_DIR . DIRECTORY_SEPARATOR . '*.json');
    $jobs = [];
    foreach($files as $f) {
        $m = json_decode(file_get_contents($f), true);
        if (!$m) continue;
        $outFile = $SCANS_DIR . DIRECTORY_SEPARATOR . $m['outfile'];
        $running = false;
        if (!empty($m['pid'])) {
            $check = shell_exec('ps -p ' . escapeshellarg($m['pid']) . ' 2>/dev/null | wc -l');
            if (intval(trim($check)) > 1) $running = true;
        }
        $jobs[] = [
            'id'=>$m['id'], 'tool'=>$m['tool'], 'target'=>$m['target'], 'pid'=>$m['pid'], 'running'=>$running, 'started'=>$m['started'], 'started_human'=>date('Y-m-d H:i:s',$m['started'])
        ];
    }
    usort($jobs, function($a,$b){ return $b['started'] - $a['started']; });
    echo json_encode(['ok'=>true,'jobs'=>$jobs]);
    exit;
}
?>
