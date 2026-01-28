<?php
include("../database_connection.php");

if (!isset($_GET['id'])) {
    die("Lesson ID not provided.");
}

$lesson_id = intval($_GET['id']);

// Fetch lesson
$stmt = $conn->prepare("SELECT lesson_id, header, content FROM lessons WHERE lesson_id=?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) {
    die("Lesson not found.");
}

// Fetch all questions for this lesson
$qstmt = $conn->prepare("SELECT * FROM questions WHERE lesson_id=?");
$qstmt->bind_param("i", $lesson_id);
$qstmt->execute();
$questions = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle lesson update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $header = $_POST['header'];
    $content = $_POST['content'];

    // Update lesson
    $updateLesson = "UPDATE lessons SET header=?, content=? WHERE lesson_id=?";
    $stmt1 = $conn->prepare($updateLesson);
    $stmt1->bind_param("ssi", $header, $content, $lesson_id);
    $stmt1->execute();

    // Update existing questions
    if (!empty($_POST['questions'])) {
        foreach ($_POST['questions'] as $index => $question) {
            $answers = $_POST['answers'][$index];
            $correct_answer = $_POST['correct_answer'][$index];
            $qid = $_POST['id'][$index];

            if ($qid) {
                // Update
                $qsql = "UPDATE questions SET questions=?, answers=?, correct_answer=? WHERE id=?";
                $uq = $conn->prepare($qsql);
                $uq->bind_param("sssi", $question, $answers, $correct_answer, $qid);
                $uq->execute();
            } else {
                // Insert new
                $qsql = "INSERT INTO questions (lesson_id, questions, answers, correct_answer) VALUES (?,?,?,?)";
                $iq = $conn->prepare($qsql);
                $iq->bind_param("isss", $lesson_id, $question, $answers, $correct_answer);
                $iq->execute();
            }
        }
    }

    header("Location: view_lessons.php?success=1");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Lesson</title>
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
                <li class="nav-item"><a class="nav-link" href="add_question.php"><i class="bi bi-question-circle"></i> Add Question</a></li>
                
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
    <h2>Edit Lesson</h2>
    <form method="post">
    <div class="mb-3">
        <label class="form-label">Lesson Title</label>
        <input type="text" name="header" class="form-control" value="<?= htmlspecialchars($lesson['header']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($lesson['content']) ?></textarea>
    </div>

    <h4>Questions</h4>
    <?php foreach ($questions as $q): ?>
        <div class="border rounded p-3 mb-3">
            <input type="hidden" name="id[]" value="<?= $q['id'] ?>">
            <div class="mb-2">
                <label>Question</label>
                <input type="text" name="questions[]" class="form-control" value="<?= htmlspecialchars($q['questions']) ?>">
            </div>
            <div class="mb-2">
                <label>Answers (comma separated)</label>
                <input type="text" name="answers[]" class="form-control" value="<?= htmlspecialchars($q['answers']) ?>">
            </div>
            <div class="mb-2">
                <label>Correct Answer</label>
                <input type="text" name="correct_answer[]" class="form-control" value="<?= htmlspecialchars($q['correct_answer']) ?>">
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Extra empty fields for adding new question -->
    <div class="border rounded p-3 mb-3">
        <input type="hidden" name="[]" value="">
        <div class="mb-2">
            <label>New Question</label>
            <input type="text" name="questions[]" class="form-control">
        </div>
        <div class="mb-2">
            <label>Answers (comma separated)</label>
            <input type="text" name="answers[]" class="form-control">
        </div>
        <div class="mb-2">
            <label>Correct Answer</label>
            <input type="text" name="correct_answer[]" class="form-control">
        </div>
    </div>

    <button type="submit" class="btn btn-success">Update</button>
    <a href="view_lessons.php" class="btn btn-secondary">Cancel</a>
</form>

</div>
</body>
</html>
