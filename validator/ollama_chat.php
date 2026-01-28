<?php
$responseText = "";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["user_input"])) {
    $url = "http://localhost:11434/api/generate";

    $useStreaming = false;
    $data = [
        "model" => "llama3",
        "prompt" => $_POST["user_input"],
        "stream" => $useStreaming
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    if ($result) {
        if ($useStreaming) {
            $responseText = "";
            $lines = explode("\n", trim($result));
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $jsonLine = json_decode($line, true);
                    if (isset($jsonLine["response"])) {
                        $responseText .= $jsonLine["response"];
                    }
                }
            }
        } else {
            $json = json_decode($result, true);
            $responseText = $json["response"] ?? "No response from model.";
        }
    } else {
        $responseText = "Error: Could not connect to Ollama.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validator - SecuScan</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            BACKGROUND: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .chat-container {
            max-width: 900px;
            margin: 0 auto;
            margin-top: 0px;
            padding-top: 120px;
        }
        .card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .response-box {
            background: #2d2d2d;
            color: #0f0;
            padding: 1.5rem;
            border-radius: 12px;
            max-height: 500px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            line-height: 1.6;
            margin-top: 20px;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.3);
        }
        .input-area {
            background: white;
            padding: 2rem;
        }
        textarea {
            border-radius: 12px !important;
            border: 2px solid #e0e0e0 !important;
            resize: none;
            font-size: 1.1rem;
        }
        textarea:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
        }
        .send-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .send-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .navbar { background: rgba(255,255,255,0.95) !important; backdrop-filter: blur(10px); }
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


<div class="chat-container">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0"><i class="fas fa-robot me-3"></i>AI Validator</h2>
            <p class="mb-0 opacity-75">Powered by LLaMA3 — Ask anything about your scans, vulnerabilities, or security</p>
        </div>
        
        <div class="input-area">
            <form method="post">
                <div class="mb-4">
                    <textarea 
                        name="user_input" 
                        class="form-control" 
                        rows="8" 
                        placeholder="Paste your scan result here or ask a security question...&#10;e.g. 'Explain this Nmap output' or 'Is this port dangerous?'" 
                        required><?php echo htmlspecialchars($_POST['user_input'] ?? ''); ?></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn send-btn text-white">
                        <i class="fas fa-paper-plane me-2"></i> Send to AI
                    </button>
                </div>
            </form>

            <?php if (!empty($responseText)): ?>
                <div class="response-box mt-4">
                    <strong class="text-warning"><i class="fas fa-brain me-2"></i>AI Response:</strong>
                    <div class="mt-3"><?= nl2br(htmlspecialchars($responseText)) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>