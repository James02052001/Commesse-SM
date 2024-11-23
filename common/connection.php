<?php
    include "config.php";
    $conn = new mysqli($db_Host, $db_User, $db_Pass, $db_Name);
    if($conn->connect_error){
        die("hai cappellato ".$conn->connect_error);
    }

?>