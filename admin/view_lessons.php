<?php
include("../database_connection.php"); 

// Fetch lessons with their related questions
$sql = "SELECT l.lesson_id, l.header, l.content, q.questions, q.answers, q.correct_answer
        FROM lessons l
        LEFT JOIN questions q ON l.lesson_id = q.lesson_id
        ORDER BY l.lesson_id DESC";

$result = $conn->query($sql);

// Group questions under lessons
$lessons = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['lesson_id'];
    if (!isset($lessons[$id])) {
        $lessons[$id] = [
            'lesson_id' => $id,
            'header' => $row['header'],
            'content' => $row['content'],
            'questions' => []
        ];
    }
    if (!empty($row['questions'])) {
        $lessons[$id]['questions'][] = [
            'question' => $row['questions'],
            'answers' => $row['answers'],
            'correct_answer' => $row['correct_answer']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Lessons</title>
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
    <h2>All Lessons</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Lesson added successfully!</div>
    <?php endif; ?>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Lesson</th>
                <th>Content</th>
                <th>Questions</th>
                <th>Answers</th>
                <th>Correct Answer</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lessons as $lesson): ?>
            <tr>
                <td><?= htmlspecialchars($lesson['header']) ?></td>
                <td><?= substr(htmlspecialchars($lesson['content']), 0, 100) ?>...</td>
                <td>
                    <?php if (!empty($lesson['questions'])): ?>
                        <ul class="mb-0">
                            <?php foreach ($lesson['questions'] as $q): ?>
                                <li><?= htmlspecialchars($q['question']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        No Question
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($lesson['questions'])): ?>
                        <ul class="mb-0">
                            <?php foreach ($lesson['questions'] as $q): ?>
                                <li><?= htmlspecialchars($q['answers']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($lesson['questions'])): ?>
                        <ul class="mb-0">
                            <?php foreach ($lesson['questions'] as $q): ?>
                                <li><?= htmlspecialchars($q['correct_answer']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td >
                    <a style="margin-bottom:5px;" href="edit_lesson.php?id=<?= $lesson['lesson_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a style="margin-bottom:5px;"  href="add_question.php?id=<?= $lesson['lesson_id'] ?>" class="btn btn-sm btn-info">Add Question</a>
                    <a style="margin-bottom:5px;"  href="delete_lesson.php?id=<?= $lesson['lesson_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lesson?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="create_lesson.php" class="btn btn-primary">Add New Lesson</a>
    <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
