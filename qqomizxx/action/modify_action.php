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

        $id  = isset($_POST['id']) ? $_POST['id'] : "";
        $bpwd = isset($_POST['bpwd']) ? $_POST['bpwd'] : "";
        $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : "";
        $pwdCheck = isset($_POST['pwdCheck']) ? $_POST['pwdCheck'] : "";

        if($id=="" || $pwd=="" || $pwdCheck=="") {
            throw new Exception("아이디, 패스워드를 입력해주세요.");
        }
        if($pwd != $pwdCheck) {
            throw new Exception("패스워드가 일치하지 않습니다.");
        }

        $query = "SELECT pwd FROM shop_admin_member WHERE id = '$id'";
        $statement = $db->prepare($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $db_pwd = isset($row['pwd']) ? $row['pwd'] : '';
        if(!password_verify($bpwd, $db_pwd)) {
            throw new Exception("현재 패스워드가 일치하지 않습니다.");
        }

        $pwd = password_hash($pwd, PASSWORD_DEFAULT);
        $query = "UPDATE shop_admin_member SET pwd = '$pwd' WHERE id = '$id'";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();
        
        if($rowCount == 0) {
            throw new Exception("처리 실패");
        }

        movepage("../members.php", "처리 완료");

    } catch(Exception $e) {
        msgback($e->getMessage());
    }


?>