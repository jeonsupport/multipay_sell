<?php

    include_once "../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $seq_no  = isset($_POST['seq_no']) ? $_POST['seq_no'] : "";


    try {

   
        // ------------------------------------------------------------------------------------------
        if($seq_no == "") throw new Exception("잘못된 접근 입니다.");
        
        $query = "UPDATE shop_user_purchase_list SET res_flag = 3 WHERE seq_no = :seq_no AND res_flag = 0";
        $statement = $db->prepare($query);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) throw new Exception("db error");

        $result_array = array(
            'status' => 1,
            'msg'    => 'ok'
        );

        echo parseJson($result_array);
        

    } catch(Exception $e) {
        echo parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(PDOException $e) {
        echo parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

