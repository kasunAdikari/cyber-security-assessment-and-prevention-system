<?php
include("../database_connection.php");

if (!isset($_GET['id'])) {
    die("Lesson ID not provided.");
}

$lesson_id = intval($_GET['id']);

// Delete related questions first
$stmt1 = $conn->prepare("DELETE FROM questions WHERE lesson_id=?");
$stmt1->bind_param("i", $lesson_id);
$stmt1->execute();

// Delete lesson
$stmt2 = $conn->prepare("DELETE FROM lessons WHERE lesson_id=?");
$stmt2->bind_param("i", $lesson_id);
$stmt2->execute();

header("Location: view_lessons.php?success=1");
exit();
?>
