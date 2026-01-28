<?php
include("../database_connection.php"); // Adjust path as needed

if (isset($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);

    $sql = "SELECT subject.subject_name, 
            subject.grade,teacher.teacher_name,
            teacher.image,institute.institute_name
            FROM class
            JOIN subject ON class.subject_id = subject.subject_id
            JOIN teacher ON class.teacher_id = teacher.teacher_id
            JOIN institute ON class.institute_id = institute.institute_id
            WHERE subject.subject_name LIKE '%$q%' 
            OR teacher.teacher_name LIKE '%$q%' 
            LIMIT 10
    ";

    $result = mysqli_query($conn, $sql);

    $suggestions = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = [
            "subject" => $row['subject_name'],
            "teacher" => $row['teacher_name'],
            "grade" => $row['grade'],
            "institute_name" => $row['institute_name'],
            "image"=> $row["image"]
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($suggestions);
}
?>
