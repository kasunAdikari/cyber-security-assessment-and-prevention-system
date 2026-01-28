<?php
include("../database_connection.php");

if (!isset($_GET['id'])) {
    die("Lesson ID not provided.");
}

$lesson_id = intval($_GET['id']);

// Fetch lesson to display its title
$stmt = $conn->prepare("SELECT header FROM lessons WHERE lesson_id=?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$result = $stmt->get_result();
$lesson = $result->fetch_assoc();

if (!$lesson) {
    die("Lesson not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $answers = $_POST['answers'];
    $correct_answer = $_POST['correct_answer'];

    $sql = "INSERT INTO questions (lesson_id, questions, answers, correct_answer) VALUES (?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $lesson_id, $question, $answers, $correct_answer);

    if ($stmt->execute()) {
        header("Location: view_lessons.php?success=1");
        exit();
    } else {
        $error = "Error adding question: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Add Question to Lesson: <span class="text-primary"><?= htmlspecialchars($lesson['header']) ?></span></h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Question</label>
            <input type="text" name="question" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Answers (comma separated)</label>
             <textarea name="answers" class="form-control" rows="6" placeholder="e.g. A, B, C, D" required></textarea>
            
        </div>

        <div class="mb-3">
            <label class="form-label">Correct Answer</label>
            <input type="text" name="correct_answer" class="form-control" placeholder="e.g. A" required>
        </div>

        <button type="submit" class="btn btn-success">Add Question</button>
        <a href="view_lessons.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
