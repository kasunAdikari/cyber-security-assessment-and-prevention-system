<?php
session_start();
ini_set('max_execution_time', 0);
set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "This endpoint accepts POST only.";
    exit;
}

$scan_text = $_POST['scan_text'] ?? '';
$case_title = trim($_POST['case_title'] ?? 'Scan Case');
$sensitivity = $_POST['sensitivity'] ?? 'private';

$scan_text ="Host: 119.235.12.202
State: up
Port: 80/tcp  State: open
Port: 113/tcp  State: closed
Port: 443/tcp  State: open
Port: 5000/tcp  State: open";

if (strlen($scan_text) < 5) {
    echo "<p>No scan output provided.</p>";
    exit;
}

$prompt = "You are a professional security engineer. I will give you raw port/service/vuln scanner output. "
    . "Analyze it and produce a prioritized remediation plan. For each issue you find, provide:\n"
    . "1) short title\n2) risk score (High/Med/Low) and CVSS-like short note\n3) concise remediation steps (commands/configuration snippets where applicable)\n4) any relevant CVE IDs or links, and MITRE ATT&CK techniques if applicable\n5) expected impact of the fix and rollback notes\n\n"
    . "If information is missing, say what extra info is needed. Do not attempt remote exploit code. "
    . "Return the answer as JSON with a top-level 'issues' array, each issue with fields: title, risk, remediation, commands, references, notes.\n\n"
    . "Begin analysis now. Raw scanner output:\n\n" . $scan_text;

$payload = json_encode([
    "model" => "llama3",
    "prompt" => $prompt,
    "stream" => false
]);

$ollama_url = "http://127.0.0.1:11434/api/generate";

$ch = curl_init($ollama_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);

$response = curl_exec($ch);
$curl_err = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curl_err || $http_code !== 200) {
    $error_msg = $curl_err ?: "HTTP $http_code";
    echo "<pre>Ollama API Error: $error_msg\n\nResponse:\n" . htmlspecialchars($response) . "</pre>";
    exit;
}

// Handle streamed response
$chunks = array_filter(array_map('trim', explode("\n", $response)));
$model_text = '';
foreach ($chunks as $chunk) {
    $data = json_decode($chunk, true);
    if (isset($data['response'])) {
        $model_text .= $data['response'];
    }
}
if (empty($model_text)) $model_text = $response;

// Try to parse model's JSON output
$issues = [];
$parsed_json = json_decode($model_text, true);
if (json_last_error() === JSON_ERROR_NONE && isset($parsed_json['issues']) && is_array($parsed_json['issues'])) {
    $issues = $parsed_json['issues'];
}

$_SESSION['scan_results'] = [
    'case_title' => $case_title,
    'sensitivity' => $sensitivity,
    'issues' => $issues,
    'model_text' => $model_text,
    'generated_at' => date('F j, Y \a\t H:i')
];
?>
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Security Scan Results - <?= htmlspecialchars($case_title) ?></title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .risk-high { background-color: #f8d7da; border-left: 5px solid #dc3545; }
        .risk-medium { background-color: #fff3cd; border-left: 5px solid #ffc107; }
        .risk-low { background-color: #d1ecf1; border-left: 5px solid #17a2b8; }
        .card-header { font-weight: 600; }
        pre { background: #f1f1f1; padding: 12px; border-radius: 6px; font-size: 0.9em; }
        .badge-risk { font-size: 0.9em; padding: 0.5em 1em; border-radius: 50px; }
    </style>
</head>
<body>
      <!-- Navbar -->
<?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => '../scanner/scan.php',
    'Validator' => 'ollama_chat.php',
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
<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-6 fw-bold text-primary">
                <i class="bi bi-shield-check me-3"></i>Security Scan Analysis
            </h1>
            <p class="lead text-muted"><?= htmlspecialchars($case_title) ?></p>
            <small class="text-muted">Generated on <?= date('F j, Y \a\t H:i') ?> | Sensitivity: <?= ucfirst(htmlspecialchars($sensitivity)) ?></small>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary me-2" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <button class="btn btn-outline-primary me-2" onclick="copyToClipboard()">
                <i class="bi bi-clipboard"></i> Copy
            </button>
            <button class="btn btn-success" onclick="downloadResult()">
                <i class="bi bi-download"></i> Download
            </button>
            <a class="btn btn-danger" href="export_pdf.php" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <?php if (!empty($issues)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> 
            <strong><?= count($issues) ?> security issue(s)</strong> identified and prioritized by AI analysis.
        </div>

        <?php foreach ($issues as $idx => $issue): 
            $title = $issue['title'] ?? "Issue #" . ($idx + 1);
            $risk = strtoupper($issue['risk'] ?? 'UNKNOWN');
            $risk_class = match($risk) {
                'HIGH' => 'risk-high badge bg-danger',
                'MEDIUM', 'MED' => 'risk-medium badge bg-warning text-dark',
                'LOW' => 'risk-low badge bg-info text-dark',
                default => 'badge bg-secondary'
            };
        ?>
            <div class="card mb-4 shadow-sm <?= strpos($risk_class, 'high') !== false ? 'border-danger' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center <?= strpos($risk_class, 'high') !== false ? 'bg-danger text-white' : 'bg-light' ?>">
                    <span class="fs-5"><?= htmlspecialchars($title) ?></span>
                    <span class="badge-risk <?= $risk_class ?>"><?= htmlspecialchars($risk) ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($issue['remediation'])): ?>
                        <h6 class="mt-3"><i class="bi bi-tools text-primary"></i> Recommended Remediation</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($issue['remediation'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($issue['commands'])): ?>
                        <h6 class="mt-4"><i class="bi bi-terminal text-success"></i> Commands / Configuration</h6>
                        <div class="accordion" id="cmdAccordion<?= $idx ?>">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#cmdCollapse<?= $idx ?>">
                                        Show exact commands
                                    </button>
                                </h2>
                                <div id="cmdCollapse<?= $idx ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <pre><?= htmlspecialchars(is_array($issue['commands']) ? implode("\n\n", $issue['commands']) : $issue['commands']) ?></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($issue['references'])): ?>
                        <h6 class="mt-4"><i class="bi bi-link-45deg text-info"></i> References</h6>
                        <ul class="list-unstyled">
                            <?php foreach ((array)$issue['references'] as $ref): ?>
                                <li class="mb-2"><i class="bi bi-box-arrow-up-right me-2"></i>
                                    <?= preg_match('/^https?:\/\//', $ref) 
                                        ? '<a href="'.htmlspecialchars($ref).'" target="_blank">'.htmlspecialchars($ref).'</a>'
                                        : htmlspecialchars($ref) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($issue['notes'])): ?>
                        <h6 class="mt-4"><i class="bi bi-info-circle text-warning"></i> Additional Notes</h6>
                        <p class="text-muted small"><?= nl2br(htmlspecialchars($issue['notes'])) ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted small">
                    Issue <?= $idx + 1 ?> of <?= count($issues) ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>No structured issues detected.</strong> Showing raw model output below.
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5><i class="bi bi-chat-square-text"></i> Raw AI Response</h5>
            </div>
            <div class="card-body">
                <pre class="mb-0"><?= htmlspecialchars($model_text) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard() {
    const text = document.body.innerText;
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = 11;
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <strong class="me-auto">Copied!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">Report copied to clipboard.</div>
            </div>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}

function downloadResult() {
    const text = document.body.innerText;
    const blob = new Blob([text], {type: 'text/plain'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'security_scan_report_<?= preg_replace('/[^a-z0-9]/i', '_', $case_title) ?>.txt';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>