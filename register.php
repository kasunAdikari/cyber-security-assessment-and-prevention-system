<?php
include 'database_connection.php';

$alert = '';

if (isset($_POST["register"])) {
    $full_name = trim($_POST["full_name"] ?? '');
    $username  = trim($_POST["username"] ?? '');
    $email     = trim($_POST["email"] ?? '');
    $password  = $_POST["password"] ?? '';
    $confirm   = $_POST["confirmPassword"] ?? '';
    $phone  = $_POST["phone"] ?? '';
    $address   = $_POST["address"] ?? '';

    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($confirm) || empty($phone) || empty($address)) {
        $alert = '<div class="alert alert-warning">Please fill all fields</div>';
    } elseif ($password !== $confirm) {
        $alert = '<div class="alert alert-danger">Passwords do not match!</div>';
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $alert = '<div class="alert alert-danger">Username already taken</div>';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (full_name, username, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssss", $full_name, $username, $email, $hash, $phone, $address);

            if ($insert->execute()) {
                $alert = '<div class="alert alert-success">Account created successfully! <a href="login.php">Login now →</a></div>';
            } else {
                $alert = '<div class="alert alert-danger">Something went wrong. Try again.</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register • SecuScan</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea; --secondary: #764ba2; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-box {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            margin-top: 100px;
            padding: 20px 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.2);
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
            margin-bottom: 2rem;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 13px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(102,126,234,0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 14px;
         
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(40,167,69,0.4);
        }
        .btn-register:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(40,167,69,0.6);
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-link a {
            color: var(--primary);
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
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

<div class="register-box">
    <div class="text-center mb-4">
        <div class="logo">
            <i class="fas fa-shield-alt fa-2x text-white"></i>
        </div>
        <h2>Create Account</h2>
        <p class="subtitle">Join SecuScan and start your cybersecurity journey</p>
    </div>

    <?= $alert ?>

    <form method="post" action="">
        <div class="mb-3">
          
            <input type="text" placeholder="Full Name" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            
            <input type="text" placeholder="Username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
           
            <input type="email" placeholder="Email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
           
            <input type="text" placeholder="Phone number" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
           
            <input type="text" placeholder="address" name="address" class="form-control" required>
        </div>
        <div class="mb-3">
           
            <input type="password" placeholder="Password" name="password" class="form-control" required>
        </div>
        <div class="mb-4">
          
            <input type="password" placeholder="Confirm password" name="confirmPassword" class="form-control" required>
        </div>

        <button type="submit" name="register" class="btn btn-register text-white w-100">
            <i class="fas fa-user-plus me-2"></i> Register Now
        </button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login here →</a>
    </div>
</div>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>