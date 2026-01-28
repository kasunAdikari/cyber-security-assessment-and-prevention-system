<?php
include("../database_connection.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    //$file_name = $_POST['file_name'];
  

    $sql = "INSERT INTO lessons (header, content)
            VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss",$title, $content);

    if ($stmt->execute()) {
        header("Location: view_lessons.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
