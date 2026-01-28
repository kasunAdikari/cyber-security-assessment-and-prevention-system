<?php
session_start();
// Simple login check (you can improve later)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 15px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Lessons Management -->
                <li class="nav-item"><a class="nav-link" href="view_lessons.php"><i class="bi bi-journal-text"></i> View Lessons</a></li>
                <li class="nav-item"><a class="nav-link" href="create_lesson.php"><i class="bi bi-plus-circle"></i> Add Lesson</a></li>
                
                <!-- Questions Management -->

                
                <!-- Future Links -->
                <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="bi bi-people"></i> Manage Users</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
            </ul>

            <!-- Right Side: Logout -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-danger btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Header Section -->
<div class="container mt-4">
    <div >
        <h1>Welcome, Admin</h1>
  
    </div>
<br>

    <!-- Dashboard Cards -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm p-3">
                <i class="bi bi-plus-circle"></i>
                <h5 class="card-title">Create Lesson</h5>
                <p class="text-muted">Add a new lesson for your modules.</p>
                <a href="create_lesson.php" class="btn btn-primary">Go</a>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center shadow-sm p-3">
                <i class="bi bi-journal-text"></i>
                <h5 class="card-title">View Lessons</h5>
                <p class="text-muted">Edit or delete existing lessons.</p>
                <a href="view_lessons.php" class="btn btn-success">Go</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm p-3">
                <i class="bi bi-people"></i>
                <h5 class="card-title">Manage Users</h5>
                <p class="text-muted">View, edit, or remove users.</p>
                <a href="manage_users.php" class="btn btn-info">Go</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm p-3">
                <i class="bi bi-graph-up"></i>
                <h5 class="card-title">Reports</h5>
                <p class="text-muted">Check system performance and progress.</p>
                <a href="reports.php" class="btn btn-dark">Go</a>
            </div>
        </div>

    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
