<?php

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "cyber";
$conn = "";


try{
    $conn = mysqli_connect($db_server,$db_user,$db_pass,$db_name);
}catch(mysqli_sql_exception){
    echo "SQL error";
}

/* if($conn){
    echo "you are connected";
}else{
    echo "you are not connected";
}*/



?>