<?php
// wsl_nmap_scan.php
// Minimal working WSL + PHP nmap scanner

// -------------------- CONFIG --------------------
// Target to scan (default)
$target = "119.235.12.202";
$target = escapeshellarg($target);

// Full path to wsl.exe
$wslPath = "C:\\Windows\\System32\\wsl.exe";

// Nmap command inside WSL
$cmd = "$wslPath nmap -Pn -sS -T4 $target";

// -------------------- EXECUTE --------------------
// Try to execute command
$output = shell_exec($cmd);

// -------------------- DISPLAY --------------------
if ($output === null) {
    echo "<h3>Error: PHP could not execute WSL command.</h3>";
    echo "<p>Check that <code>shell_exec</code> is enabled in php.ini and Apache runs as your Windows user.</p>";
} else {
    echo "<h3>Scan result for $target</h3>";
    echo "<pre>$output</pre>";
}
?>
