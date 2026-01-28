<?php
session_start();

// === CRITICAL FIX: Use user_id from session, NOT username ===
if (!isset($_SESSION['user_id'])) {
    // If you only have username in session right now, we fetch the ID first
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    
    // Temporary fallback: get user_id from username (only if you don't store user_id yet)
    require_once '../database_connection.php';
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $stmt->bind_result($fetched_id);
    if (!$stmt->fetch()) {
        die("Session error: User not found.");
    }
    $_SESSION['user_id'] = $fetched_id; // now we have it forever
    $user_id = $fetched_id;
} else {
    $user_id = $_SESSION['user_id'];
}
// ============================================================

require_once '../database_connection.php';

$errors = [];
$success = false;

// Fetch current user data (now including password hash for update logic)
$stmt = $conn->prepare("SELECT username, email, full_name, password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';

    // Validation
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";

    // Check uniqueness (exclude current user)
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Username or email is already taken.";

    // Password change (optional)
    $password_hash = $user['password']; // keep current hash by default
    if (!empty($password) || !empty($password2)) {
        if ($password !== $password2) {
            $errors[] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    // Save if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, password = ? WHERE user_id = ?");
        $full_name = $full_name === '' ? null : $full_name; // handle NULL properly
        $stmt->bind_param("ssssi", $username, $email, $full_name, $password_hash, $user_id);

        if ($stmt->execute()) {
            $success = true;
            $_SESSION['username'] = $username; // update session username too

            // Refresh user data
            $user['username'] = $username;
            $user['email'] = $email;
            $user['full_name'] = $full_name;
        } else {
            $errors[] = "Failed to save changes. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - SecuScan</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f4f4; padding-top: 70px; }
        .card { max-width: 600px; margin: 2rem auto; }
    </style>
</head>
<body>

<?php
include "../navbar.php";
$links = [
    'Home' => '../index.php',
    'Scanner' => '../scanner/scan.php',
    'Validator' => '../validator/ollama_chat.php',
    'Register' => '../register.php',
    'Login' => '../login.php'
];
if (isset($_SESSION['user_id'])) {
    unset($links['Register'], $links['Login']);
    $links['Dashboard'] = 'dashboard.php';
    $links['Logout'] = '../logout.php';
}
Nav_Bar($links);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Edit Your Account</h4>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success">Account updated successfully!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Name <small class="text-muted">(optional)</small></label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                </div>

                <hr>
                <div class="mb-3">
                    <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password2" class="form-control" placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>