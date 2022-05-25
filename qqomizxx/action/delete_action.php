<?php


    // DB connect
    include_once "../../db_connecter.php";
    include_once "../../inc.php";
 
    $sqlConnecter = new MySQL_Connecter();
    $db = $sqlConnecter->ConnectServer();


    try {
        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : '';

        $query = "DELETE FROM shop_admin_member WHERE seq_no = $seq_no AND authority = 0";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if($rowCount == 0) {
            throw new Exception("처리 실패");
        }
        
        movepage("../members.php", "처리 성공");

    } catch(Exception $e) {
        msgback($e->getMessage());
    }
?>