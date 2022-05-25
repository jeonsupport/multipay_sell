<?php
    session_start();

    include_once "../db_connecter.php";
    include_once "../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    try {

        $page = isset($_POST['page']) ? $_POST['page'] : "";
        $seq_no = isset($_POST['seq_no']) ? $_POST['seq_no'] : "";
        $sch_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : "";
        $sch_keyword = isset($_POST['sch_keyword']) ? $_POST['sch_keyword'] : "";
        $pw = isset($_POST['pw']) ? $_POST['pw'] : "";

        $query = "SELECT * FROM shop_notice_board WHERE seq_no = $seq_no AND use_flag = 0";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $dbPWD = isset($row['pwd']) ? $row['pwd'] : "";

        if(password_verify($pw, $dbPWD)) {
            $_SESSION['seq_no'] = $seq_no;            
            movepage("../qna_detail.php?page=$page&seq_no=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword");
        } else {
            throw new Exception("비밀번호를 확인해주세요.");
        }
        

    } catch(PDOException $e) {
        msgback("database query error");
    } catch(Exception $e) {
        msgback($e->getMessage());
    }


?>