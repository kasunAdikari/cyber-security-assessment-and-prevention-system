<?php include("../database_connection.php");  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Lesson</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    
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

<div class="container mt-5">
    <h2>Create Lesson</h2>
    <form action="save_lesson.php" method="POST">

        <div class="mb-3">
            <label class="form-label">Lesson Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6" required></textarea>
        </div>
        <!--
        <div class="mb-3">
            <label class="form-label">File name</label>
            <input type="text" name="file_name" class="form-control" required>
        </div> -->

    
        <button type="submit" class="btn btn-primary">Save Lesson</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
