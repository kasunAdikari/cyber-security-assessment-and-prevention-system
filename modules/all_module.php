<?php
session_start();
require_once __DIR__ . '/db.php';

// Fetch all lessons
$stmt = $pdo->query("SELECT lesson_id, header, SUBSTRING(content, 1, 200) AS snippet FROM lessons ORDER BY lesson_id ASC");
$lessons = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Modules - SecuScan</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e6edf3;
        }
        .navbar {
            background: rgba(255,255,255,0.95) !important;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }
        .page-header {
            padding: 120px 0 80px;
            text-align: center;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6));
            margin-bottom: 3rem;
        }
        .page-header h1 {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(90deg, #fff, #ccd6f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .page-header p {
            font-size: 1.4rem;
            max-width: 800px;
            margin: 1rem auto 0;
            opacity: 0.95;
        }
        .module-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .module-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(102,126,234,0.4);
            border-color: rgba(102,126,234,0.6);
        }
        .module-card .card-body {
            padding: 2rem;
        }
        .module-card h4 {
            color: #ffffffff;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .module-card p {
            color: #000000ff;
            line-height: 1.7;
            flex-grow: 1;
        }
        .btn-learn {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 14px 32px;
            
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 8px 20px rgba(40,167,69,0.4);
        }
        .btn-learn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(40,167,69,0.6);
            background: linear-gradient(135deg, #218838, #1eaa80);
        }
        .no-modules {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            border: 1px dashed rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

<?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => '../scanner/scan.php',
    'Validator' => '../validator/ollama_chat.php',
    'Learn' => 'all_module.php',
    'Register' => '../register.php',
    'Login' => '../login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = '../user/dashboard.php';
    $links['Logout'] = '../logout.php';
}
Nav_Bar($links);
?>
<!-- Hero Header -->
<div class="page-header">
    <div class="container">
        <h1>Cybersecurity Learning Hub</h1>
        <p>Master ethical hacking, penetration testing, and defensive security with hands-on modules designed for real-world application.</p>
    </div>
</div>

<!-- Modules Grid -->
<div class="container pb-5">
    <?php if ($lessons): ?>
        <div class="row g-5">
            <?php foreach ($lessons as $lesson): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="module-card h-100 d-flex flex-column">
                        <div class="card-body d-flex flex-column">
                            <h4>
                               
                                <?= htmlspecialchars($lesson['header']) ?>
                            </h4>
                            <p class="flex-grow-1">
                                <?= nl2br(htmlspecialchars($lesson['snippet'])) ?>
                                <?= strlen($lesson['snippet']) >= 200 ? '...' : '' ?>
                            </p>
                            <div class="mt-auto text-end">
                                <a href="intro.php?lesson_id=<?= (int)$lesson['lesson_id'] ?>" class="btn btn-learn text-white">
                                    <i class="fas fa-play-circle me-2"></i> Start Learning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-modules">
            <i class="fas fa-book-open fa-5x text-muted mb-4 opacity-50"></i>
            <h3>No modules available yet</h3>
            <p class="opacity-75">Check back soon — new cybersecurity lessons are being added regularly!</p>
        </div>
    <?php endif; ?>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>