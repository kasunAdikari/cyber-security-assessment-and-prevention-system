<?php
session_start();
require_once __DIR__ . '/modules/db.php';

// Fetch all lessons
$stmt = $pdo->query("SELECT lesson_id, header, SUBSTRING(content, 1, 160) AS snippet FROM lessons ORDER BY lesson_id ASC LIMIT 6");
$lessons = $stmt->fetchAll();

if (empty($_SESSION["username"])) {
    $_SESSION["username"] = 'Guest';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuScan - Cybersecurity Learning & Scanning Platform</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --dark: #2d1b3d;
            --light: #f8f9fa;
        }
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
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.7)), url('images/img2.jpg') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
        }
        .hero-content {
            z-index: 2;
            color: white;
            text-shadow: 0 4px 10px rgba(0,0,0,0.6);
        }
        .hero h1 {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }
        .btn-cta {
            padding: 1rem 2.5rem;
            font-size: 1.3rem;
          
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        .section-title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(90deg, #ffffffff, #d3bceaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 1rem;
        }
        .card-lesson {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.4s;
            height: 100%;
        }
        .card-lesson:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.3);
        }
        .card-lesson .card-body {
            background: white;
            padding: 2rem;
        }
        .btn-learn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
          
            font-weight: 600;
        }
        .btn-learn:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            color: white;
        }
        footer {
            background: var(--dark);
            color: #ddd;
        }
        .footer-link:hover {
            color: white !important;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php
include "navbar.php";
$links = [
    'Home' => 'index.php',
    'Scanner' => 'scanner/scan.php',
    'Validator' => 'validator/ollama_chat.php',
     'Learn' => 'modules/all_module.php',
    'Register' => 'register.php',
    'Login' => 'login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = 'user/dashboard.php';
    $links['Logout'] = 'logout.php';
}
Nav_Bar($links);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10 hero-content">
                <h1>Welcome to SecuScan</h1>
                <p class="lead fs-3 mb-5 text-light opacity-90">
                    Master Cybersecurity • Scan Networks • Learn Ethical Hacking • Powered by AI
                </p>
                <div>
                    <a href="login.php" class="btn btn-light btn-cta me-4">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-outline-light btn-cta">
                        <i class="fas fa-user-plus me-2"></i> Register Free
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Learning Modules Section -->
<section class="py-5">
    <div class="container py-5">
        <h2 class="section-title" style="color: black;">Cybersecurity Learning Modules</h2>
        <p class="text-center text-white fs-4 mb-5 opacity-90">
            Start your ethical hacking journey with hands-on, structured lessons
        </p>

        <div class="row g-5">
            <?php foreach ($lessons as $lesson): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-lesson h-100">
                        <div class="card-body d-flex flex-column">
                            <h4 class="card-title text-primary fw-bold">
                                
                                <?= htmlspecialchars($lesson['header']) ?>
                            </h4>
                            <p class="text-muted flex-grow-1">
                                <?= nl2br(htmlspecialchars($lesson['snippet'])) ?>
                                <?= strlen($lesson['snippet']) >= 160 ? '...' : '' ?>
                            </p>
                            <div class="mt-auto">
                                <a href="modules/intro.php?lesson_id=<?= (int)$lesson['lesson_id'] ?>" 
                                   class="btn btn-learn w-100">
                                    <i class="fas fa-play-circle me-2"></i> Start Learning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($lessons)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No modules available yet. Check back soon!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-light pt-5 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-white">SecuScan</h5>
                <p class="small opacity-75">
                    Your all-in-one platform for network scanning, ethical hacking education, and AI-powered vulnerability analysis.
                </p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-white">Quick Links</h5>
                <ul class="list-unstyled small">
                    <li><a href="user/dashboard.php" class="text-light footer-link">Dashboard</a></li>
                    <li><a href="scanner/scan.php" class="text-light footer-link">Port Scanner</a></li>
                    <li><a href="modules/all_module.php" class="text-light footer-link">All Modules</a></li>
                    <li><a href="validator/ollama_chat.php" class="text-light footer-link">AI Validator</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-white">Contact</h5>
                <p class="small opacity-75">
                    <i class="fas fa-envelope me-2"></i> support@secuscan.com<br>
                    <i class="fas fa-map-marker-alt me-2"></i> Colombo, Sri Lanka
                </p>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="text-center small opacity-75">
            © <?= date("Y") ?> SecuScan. Made with <i class="fas fa-heart text-danger"></i> for the cybersecurity community.
        </div>
    </div>
</footer>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>