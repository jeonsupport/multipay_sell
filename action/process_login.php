<?php
    session_start();

    include_once "../db_connecter.php";
    include_once "../inc.php";

    $sqlConnecter = new MySQL_Connecter();
    $db = $sqlConnecter->ConnectServer();


    try {

        $page = isset($_POST['page']) ? $_POST['page'] : "";
        $seq_no = isset($_POST['seqno']) ? $_POST['seqno'] : "";
        $sch_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : "";
        $sch_keyword = isset($_POST['sch_keyword']) ? $_POST['sch_keyword'] : "";
        $pw = isset($_POST['pw']) ? $_POST['pw'] : "";

        $query = "SELECT pwd FROM shop_user_purchase_list WHERE seq_no = $seq_no";
        $statement = $db->prepare($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $dbPWD = isset($row['pwd']) ? $row['pwd'] : "";

        if(password_verify($pw, $dbPWD)) {
            $_SESSION['seqno'] = $seq_no;            
            movepage("../process_detail.php?page=$page&seqno=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword");
        } else {
            throw new Exception("비밀번호를 확인해주세요.");
        }
        

    } catch(Exception $e) {
        msgback($e->getMessage());
    }


?>