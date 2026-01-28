<?php
// run_gobuster_raw.php
// Minimal script to run a gobuster directory scan inside WSL and display results.
// Edit the variables below before running.
// NOTE: Wordlist must be a plain text file (one entry per line). If your file is an archive (.tar.gz, .zip), extract it first.

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --------- EDIT THESE VARIABLES ---------
$target = 'http://aquinas.lk/'; // target URL (include scheme http:// or https://)
$wordlistWin = 'C:\\xampp\\htdocs\\cyber\\scanner\\wordlists\\rockyou.txt.tar.gz'; // Windows path to wordlist (change)
$threads = 50; // gobuster -t value
$outWin = 'C:\\xampp\\htdocs\\cyber\\scanner\\scans\\gobuster_result.txt'; // Windows path where you want the output file saved
// ---------------------------------------

/*
 How this works:
 - Converts Windows paths (C:\...) to WSL path (/mnt/c/...)
 - Builds a command like:
    wsl.exe bash -lc 'gobuster dir -u "http://..." -w "/mnt/c/..." -t 50 -o "/mnt/c/..."'
 - Runs the command and prints gobuster's stdout/stderr.
*/

// path to wsl.exe
$wslExe = 'C:\\Windows\\System32\\wsl.exe';
if (!file_exists($wslExe)) {
    echo "Error: wsl.exe not found at $wslExe\n";
    exit(1);
}

// helper: convert Windows path to WSL path (simple conversion)
function win_to_wsl_path(string $p): string {
    // normalize backslashes
    $p = str_replace('\\', '/', $p);
    // remove double slashes
    $p = preg_replace('#/+#', '/', $p);
    // match drive letter like C:/path...
    if (preg_match('#^([A-Za-z]):/(.*)$#', $p, $m)) {
        $drive = strtolower($m[1]);
        $rest = $m[2];
        return '/mnt/' . $drive . '/' . $rest;
    }
    // if it's already a unix style path, return as-is
    return $p;
}

// ensure target contains scheme
if (!preg_match('#^https?://#i', $target)) {
    $target = 'http://' . $target;
}

// convert wordlist and output paths
$wordlistWsl = win_to_wsl_path($wordlistWin);
$outWsl = win_to_wsl_path($outWin);

// basic checks
if (!file_exists($wordlistWin)) {
    echo "Warning: Wordlist not found at Windows path: $wordlistWin\n";
    echo "If the wordlist is inside WSL only, set \$wordlistWin to the Windows path (e.g. C:\\...) or use a WSL path.\n";
    // continue anyway; gobuster will error if file missing
}

$outDir = dirname($outWin);
if (!is_dir($outDir)) {
    // try to create directory
    if (!mkdir($outDir, 0777, true) && !is_dir($outDir)) {
        echo "Warning: Could not create output directory: $outDir\n";
    }
}

// build gobuster command to run inside bash -lc
// we wrap each argument in quotes to be safe
$gobCmdInner = 'gobuster dir -u ' . escapeshellarg($target)
             . ' -w ' . escapeshellarg($wordlistWsl)
             . ' -t ' . intval($threads)
             . ' -o ' . escapeshellarg($outWsl);

// for verbose output also show to stdout; gobuster writes to -o file but also prints to stdout
// run via wsl bash -lc '<command>'
$innerForBash = $gobCmdInner;

// build final command: C:\Windows\System32\wsl.exe bash -lc '<inner>'
$finalCmd = escapeshellarg($wslExe) . ' bash -lc ' . escapeshellarg($innerForBash);

// run command and capture output (this will block until gobuster finishes)
echo "Running: $finalCmd\n\n";

$output = null;
$returnVar = null;
$output = shell_exec($finalCmd . ' 2>&1');

// check result
if ($output === null) {
    echo "Error: shell_exec returned null. Check php.ini disable_functions and webserver permissions.\n";
    exit(1);
}

// print gobuster output
echo "==== Gobuster output (stdout/stderr) ====\n";
echo $output . "\n";

// if an output file was specified, show its path and (optionally) read its content
if (file_exists($outWin)) {
    echo "\nSaved output file (Windows path): $outWin\n";
    // Display first N lines (avoid dumping huge file)
    $maxBytes = 200000;
    $contents = file_get_contents($outWin, false, null, 0, $maxBytes);
    echo "\n==== Saved file preview (first {$maxBytes} bytes) ====\n";
    echo $contents . "\n";
} else {
    echo "\nNote: gobuster output file not found at $outWin (maybe gobuster failed to write). Check the gobuster output above for errors.\n";
}

?>
