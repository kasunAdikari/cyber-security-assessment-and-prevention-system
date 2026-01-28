<?php 
ini_set('max_execution_time', 600);
session_start();

// Auto-fetch user_id if missing
if (isset($_SESSION['username']) && !isset($_SESSION['user_id'])) {
    include '../database_connection.php';
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($_SESSION['user_id']);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port Scanner - SecuScan</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: rgba(255,255,255,0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .scanner-container {
            max-width: 1000px;
            margin: 120px auto 40px;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .card-header h1 {
            font-weight: 800;
             font-size: 2.8rem;
            margin: 0;
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 14px;
            font-size: 1.1rem;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .btn-scan {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 16px 50px;
            border-radius: 20px;
            font-size: 1.3rem;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-scan:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(40, 167, 69, 0.4);
        }
        pre {
            background: #1e1e1e;
            color: #0f0;
            padding: 1.5rem;
            border-radius: 16px;
            max-height: 600px;
            overflow: auto;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            line-height: 1.6;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.4);
        }
        .action-buttons .btn {
            padding: 12px 32px;
            border-radius: 50px;
            font-weight: 600;
            margin: 0 10px;
        }
        .btn-validator {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        .btn-save {
            background: linear-gradient(135deg, #28a745, #218838);
        }
    </style>
</head>
<body>

<?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => 'scan.php',
    'Validator' => '../validator/ollama_chat.php',
    'Learn' => '../modules/all_module.php',
    'Register' => '../register.php',
    'Login' => '../login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = '../user/dashboard.php';
    $links['Logout'] = 'logout.php';
}
Nav_Bar($links);
?>

<div class="scanner-container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-search-dollar me-3"></i> Advanced Port Scanner</h1>
            <p class="mb-0 opacity-90">Discover open ports, services, and potential vulnerabilities</p>
        </div>
        <div class="card-body p-5 bg-white">
            <form method="POST" class="row g-4">
                <div class="col-md-7">
                    <label class="form-label fw-bold fs-5 text-primary">Target IP / Hostname</label>
                    <input type="text" name="target" class="form-control form-control-lg" placeholder="e.g. 192.168.1.1 or scanme.nmap.org" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold fs-5 text-primary">Scan Type</label>
                    <select name="scan_type" class="form-select form-select-lg" required>
                        <option value="basic">Basic Scan (Fast)</option>
                        <option value="advanced">Advanced Scan (Detailed)</option>
                        <option value="cve">CVE Vulnerability Scan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-scan text-white">
                         Scan
                    </button>
                </div>
            </form>

            <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                <?php
                $target_raw = trim($_POST['target'] ?? '');
                $target = escapeshellarg($target_raw);
                $scanType = $_POST['scan_type'] ?? 'basic';

                if ($scanType == "advanced") {
                    $output = shell_exec("python scanner_advanced.py $target 2>&1");
                } elseif ($scanType == "cve") {
                    $output = shell_exec("python scanner_cve.py $target 2>&1");
                } else {
                    $output = shell_exec("python scanner_basic.py $target 2>&1");
                }

                $scan_output = is_string($output) ? $output : 'No output received.';
                ?>

                <hr class="my-5">

                <h3 class="text-center mb-4 text-primary fw-bold">
                   Scan Results for: <strong><?= htmlspecialchars($target_raw) ?></strong>
                </h3>
                <pre><?= htmlspecialchars($scan_output) ?></pre>

                <div class="action-buttons text-center mt-5">
                    <form method="POST" action="../validator/ollama_chat2.php" class="d-inline">
                        <input type="hidden" name="source_page" value="scan.php">
                        <textarea name="scan_text" style="display:none;"><?= htmlspecialchars($scan_output) ?></textarea>
                        <button type="submit" class="btn btn-validator text-white action-btn">
                            <i class="fas fa-robot me-2"></i> Analyze with AI
                        </button>
                    </form>

                    <?php
                    if (isset($_SESSION['user_id']) && !empty($scan_output)) {
                        include '../database_connection.php';
                        $check = $conn->prepare("SELECT scan_id FROM scan_results WHERE user_id = ? AND result = ?");
                        $check->bind_param("is", $_SESSION['user_id'], $scan_output);
                        $check->execute();
                        $already_saved = $check->get_result()->num_rows > 0;
                        $check->close();

                        if (!$already_saved) {
                            ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="save_scan" value="1">
                                <input type="hidden" name="result_text" value="<?= htmlspecialchars($scan_output) ?>">
                                <input type="hidden" name="ip_address" value="<?= htmlspecialchars($target_raw) ?>">
                                <button type="submit" class="btn btn-save text-white action-btn">
                                    <i class="fas fa-save me-2"></i> Save Result
                                </button>
                            </form>
                            <?php
                        } else {
                            echo '<span class="text-success fs-4 ms-3">Saved to History</span>';
                        }
                    }

                    if (isset($_POST['save_scan']) && isset($_SESSION['user_id'])) {
                        include '../database_connection.php';
                        $result_text = $_POST['result_text'];
                        $ip_addr = $_POST['ip_address'] ?? 'Unknown';

                        $save = $conn->prepare("INSERT INTO scan_results (user_id, result, ip_address) VALUES (?, ?, ?)");
                        $save->bind_param("iss", $_SESSION['user_id'], $result_text, $ip_addr);
                        if ($save->execute()) {
                            echo '<div class="text-center text-success fs-3 mt-4">Scan saved successfully!</div>';
                        }
                        $save->close();
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>