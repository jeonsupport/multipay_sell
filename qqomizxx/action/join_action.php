<?php
    session_start();

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
        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : "";
        $pwdCheck = isset($_POST['pwdCheck']) ? $_POST['pwdCheck'] : "";

        if($id=="" || $pwd=="" || $name=="" || $pwdCheck=="") {
            throw new Exception("아이디, 이름, 패스워드를 입력해주세요.");
        }
        if($pwd != $pwdCheck) {
            throw new Exception("패스워드가 일치하지 않습니다.");
        }

        $pwd = password_hash($pwd, PASSWORD_DEFAULT);


        $query = "
            INSERT INTO shop_admin_member(id, pwd, name, reg_date)
            SELECT * FROM (SELECT '$id' AS id, '$pwd' AS pwd, '$name' AS name, now(3) AS reg_date) AS tmp
            WHERE NOT EXISTS (
                SELECT id FROM shop_admin_member WHERE id = '$id'
            ) LIMIT 1;
        ";

        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if($rowCount == 0) {
            throw new Exception("중복된 아이디 입니다.");
        }

        movepage("../members.php", "등록 성공");

    } catch(Exception $e) {
        msgback($e->getMessage());
    } catch(PDOException $e) {
        msgback($e->getMessage());
    }


?>