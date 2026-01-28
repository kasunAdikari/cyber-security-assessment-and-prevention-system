<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Database connection (update with your actual credentials if needed)
$host = 'localhost';
$db   = 'cyber';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin Dashboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .page-header {
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        .report-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="view_lessons.php"><i class="bi bi-journal-text"></i> View Lessons</a></li>
                <li class="nav-item"><a class="nav-link" href="create_lesson.php"><i class="bi bi-plus-circle"></i> Add Lesson</a></li>

                <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="bi bi-people"></i> Manage Users</a></li>
                <li class="nav-item"><a class="nav-link active" href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="btn btn-danger btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
   

    <!-- Overview Cards -->
    <div class="row g-4 mb-5">
        <?php
        $total_users = $mysqli->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
        $total_lessons = $mysqli->query("SELECT COUNT(*) FROM lessons")->fetch_row()[0];
        $total_questions = $mysqli->query("SELECT COUNT(*) FROM questions")->fetch_row()[0];
        $total_completions = $mysqli->query("SELECT COUNT(*) FROM finished_lessons")->fetch_row()[0];
        ?>
        <div class="col-md-3">
            <div class="card text-center p-4 report-card">
                <i class="bi bi-people display-4 text-primary"></i>
                <h3><?= $total_users ?></h3>
                <p class="text-muted">Total Registered Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4 report-card">
                <i class="bi bi-journal-text display-4 text-success"></i>
                <h3><?= $total_lessons ?></h3>
                <p class="text-muted">Total Lessons</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4 report-card">
                <i class="bi bi-question-circle display-4 text-warning"></i>
                <h3><?= $total_questions ?></h3>
                <p class="text-muted">Total Quiz Questions</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-4 report-card">
                <i class="bi bi-check-circle display-4 text-info"></i>
                <h3><?= $total_completions ?></h3>
                <p class="text-muted">Lesson Completions</p>
            </div>
        </div>
    </div>

    <!-- Lesson Statistics -->
    <div class="card report-card">
        <div class="card-header bg-primary text-white">
            <h5><i class="bi bi-journal-text"></i> Lesson Statistics</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lesson Title</th>
                            <th>Created On</th>
                            <th>Times Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $lessons_query = $mysqli->query("
                            SELECT l.lesson_id, l.header, l.created_at, COUNT(f.lesson_id) as completions
                            FROM lessons l
                            LEFT JOIN finished_lessons f ON l.lesson_id = f.lesson_id
                            GROUP BY l.lesson_id
                            ORDER BY l.lesson_id
                        ");
                        while ($row = $lessons_query->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['lesson_id']}</td>
                                <td>{$row['header']}</td>
                                <td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>
                                <td>{$row['completions']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Progress -->
    <div class="card report-card mt-4">
        <div class="card-header bg-success text-white">
            <h5><i class="bi bi-person-check"></i> User Progress Overview</h5>
        </div>
        <div class="card-body">
            <?php
            $users_query = $mysqli->query("
                SELECT u.user_id, u.username, u.email, COUNT(f.lesson_id) as completed_lessons
                FROM users u
                LEFT JOIN finished_lessons f ON u.user_id = f.user_id
                GROUP BY u.user_id
            ");
            if ($users_query->num_rows == 0) {
                echo "<p>No users registered yet.</p>";
            } else {
                while ($user = $users_query->fetch_assoc()) {
                    echo "<div class='mb-3'>
                        <strong>User:</strong> {$user['username']} ({$user['email']})<br>
                        <strong>Completed Lessons:</strong> {$user['completed_lessons']} / {$total_lessons}
                    </div><hr>";
                }
            }
            ?>
        </div>
    </div>

    <!-- Quiz Performance - Introduction to Cybersecurity (Lesson 3) -->
    <div class="card report-card mt-4">
        <div class="card-header bg-warning text-dark">
            <h5><i class="bi bi-trophy"></i> Quiz Performance - Introduction to Cybersecurity (Lesson 3)</h5>
        </div>
        <div class="card-body">
            <?php
            // Count how many answers the user got correct for lesson 3
            $correct_query = $mysqli->query("
                SELECT COUNT(*) as correct_count
                FROM ongoing_lesson o
                JOIN questions q ON o.lesson_id = q.lesson_id AND o.id = q.id
                WHERE o.lesson_id = 3 AND LEFT(o.answer, LENGTH(q.correct_answer) + 1) = CONCAT(q.correct_answer, ' ')
            ");
            $correct_row = $correct_query->fetch_assoc();
            $correct = $correct_row['correct_count'];

            // Total answered questions for lesson 3
            $answered_query = $mysqli->query("SELECT COUNT(*) as answered_count FROM ongoing_lesson WHERE lesson_id = 3");
            $answered_row = $answered_query->fetch_assoc();
            $answered = $answered_row['answered_count'];

            $accuracy = $answered > 0 ? round(($correct / $answered) * 100, 1) : 0;
            ?>
            <p><strong>Questions Attempted:</strong> <?= $answered ?> / 10</p>
            <p><strong>Correct Answers:</strong> <?= $correct ?></p>
            <p><strong>Accuracy:</strong> <?= $accuracy ?>%</p>

            <?php if ($answered > 0): ?>
            <h6 class="mt-4">Detailed Answers:</h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Question ID</th>
                        <th>User Answer</th>
                        <th>Correct Answer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $detail_query = $mysqli->query("
                        SELECT o.id, o.answer AS user_answer, q.correct_answer AS correct_answer
                        FROM ongoing_lesson o
                        JOIN questions q ON o.id = q.id
                        WHERE o.lesson_id = 3
                        ORDER BY o.id
                    ");
                    while ($row = $detail_query->fetch_assoc()) {
                        $status = (substr($row['user_answer'], 0, strlen($row['correct_answer']) + 1) === $row['correct_answer'] . ' ') ? 'Wrong' : 'correct';
                        $badge = ($status === 'Correct') ? 'bg-success' : 'bg-danger';
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['user_answer']}</td>
                            <td>{$row['correct_answer']}</td>
                            <td><span class='badge {$badge}'>{$status}</span></td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Activity Summary -->
    <div class="card report-card mt-4">
        <div class="card-header bg-info text-white">
            <h5><i class="bi bi-activity"></i> System Activity Summary</h5>
        </div>
        <div class="card-body">
            <ul>
                <li>Platform launched with introductory lesson on September 18, 2025</li>
                <li>11 total lessons covering major web application vulnerabilities (OWASP-inspired)</li>
                <li>10 quiz questions available for the introductory lesson</li>
                <li>1 registered user with partial quiz progress (2 questions answered, both correct)</li>
                <li>No lessons marked as fully completed yet</li>
                <li>Tables <code>scan_results</code> and <code>fix_guide</code> are currently empty</li>
            </ul>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>