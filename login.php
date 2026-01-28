<?php
session_start();
include 'database_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '<div class="alert alert-warning">Please enter both username and password</div>';
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = "user";
                header("Location: user/dashboard.php");
                exit();
            } else {
                $error = '<div class="alert alert-danger">Invalid password</div>';
            }
        } else {
            $error = '<div class="alert alert-danger">No account found with that username</div>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • SecuScan</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .login-box {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            margin-top: 100px;
              padding: 20px 2.5rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 30px 70px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.3);
        }
         .logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1px;
            box-shadow: 0 10px 30px rgba(102,126,234,0.4);
        }
        h2 {
            text-align: center;
            font-weight: 800;
            background: linear-gradient(90deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }
        .form-control {
            border-radius: 14px;
            padding: 16px 18px;
            border: 2px solid #e0e0e0;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(102,126,234,0.25);
        }
        .input-group-text {
            border-radius: 14px 0 0 14px;
            background: #f8f9fa;
        }
        .btn-login {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 16px;
           
            font-size: 1.25rem;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(40,167,69,0.4);
        }
        .btn-login:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px rgba(40,167,69,0.6);
        }
        .register-link {
            text-align: center;
            margin-top: 2rem;
            font-size: 1.1rem;
        }
        .register-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
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
<div class="login-box">
    <div class="text-center mb-4">
        <div class="logo">
            <i class="fas fa-shield-alt fa-2x text-white"></i>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Log in to continue your cybersecurity journey</p>
    </div>

    <?= $error ?>

    <form method="post" action="">
        <div class="mb-4">
           
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control form-control-lg" placeholder="Enter your username" required>
            </div>
        </div>

        <div class="mb-4">
            
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-login text-white w-100">
            <i class="fas fa-sign-in-alt me-2"></i> Login Securely
        </button>
    </form>

    <div class="register-link">
        Don't have an account? <a href="register.php">Register for free →</a>
    </div>
</div>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>