<?php
    session_start();
    $result = session_destroy();
    if($result){
        echo "<script>location.replace('../login.php')</script>";
    }
?>