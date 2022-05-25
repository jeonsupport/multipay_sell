<?php

    include_once "../../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();
 
    $jData  = isset($_POST['sendData']) ? $_POST['sendData'] : "";
    $reqVal = isset($jData['reqVal']) ? strip_tags($jData['reqVal']) : "";
    

    try {

        // ------------------------------------------------------------------------------------------
        // 관리자 처리상태 변경
        $d_ReqValArr = explode("|", $reqVal);
        $resflag = $d_ReqValArr[0];
        $seqno = $d_ReqValArr[1];
            
        $query = "UPDATE shop_user_purchase_list SET res_flag = $resflag WHERE seq_no = $seqno";
        $statement = $db_access->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if($rowCount==0) {
            throw new Exception("변경 실패");
        }

        $result_array = array(
            'status' => 1,
            'msg' => '변경되었습니다.'
        );

        echo parseJson($result_array);
    
    } catch(Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

   

    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

