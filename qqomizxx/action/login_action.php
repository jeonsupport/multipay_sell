<?php
    session_start();

    // DB connect
    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    try {

        $id  = isset($_POST['id']) ? $_POST['id'] : "";
        $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : "";
        // $pwd = password_hash($pwd, PASSWORD_DEFAULT);

        if($id=="" || $pwd=="") {
            throw new Exception("아이디, 패스워드를 입력해주세요.");
        }


        $query = "SELECT id, pwd, name, authority FROM shop_admin_member WHERE id = '$id'";
        $statement = $db->prepare($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $rowCount = $statement->rowCount();
        $adminPass = isset($row['pwd']) ? $row['pwd'] : "";
        $adminId = isset($row['id']) ? $row['id'] : "";
        $adminName = isset($row['name']) ? $row['name'] : "";
        $authority = isset($row['authority']) ? $row['authority'] : 0;

        if($rowCount == 0) {
            throw new Exception("아이디 혹은 비밀번호를 확인해주세요.");
        }

        if(password_verify($pwd, $adminPass)) {

            $query = "UPDATE shop_admin_member SET upd_date = now(3) WHERE id = '$id'";
            $statement = $db->prepare($query);
            $statement->execute();
            $rowCount = $statement->rowCount();

            if($rowCount == 0) {
                throw new Exception("로그인 데이터베이스 에러(update)");
            }            
            $_SESSION['AdminID'] = $adminId;
            $_SESSION['AdminAuthority'] = $authority;

            movepage("../process.php");
        } else {
            throw new Exception("아이디 혹은 비밀번호를 확인해주세요.(35)");
        }

    } catch(PDOException $e) {
        msgback("로그인 데이터베이스 에러");
    } catch(Exception $e) {
        msgback($e->getMessage());
    }


?>