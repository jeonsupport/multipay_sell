<?php


    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MsSQL_Connecter();
    $db = $conn->ConnectServer();


    try {
        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        if (empty($_POST)) {
            throw new Exception('잘못된 호출입니다.');
        }
        $err_msg = '';
        $seq_no = isset($_POST['seq_no']) ? $_POST['seq_no'] : '';
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $chain_name = isset($_POST['chain_name']) ? $_POST['chain_name'] : '';
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $free_date= isset($_POST['free_date']) && !empty($_POST['free_date']) ? date('Ymd', strtotime($_POST['free_date'])) : '';
        $pay_date= isset($_POST['pay_date']) && !empty($_POST['pay_date']) ? date('Ymd', strtotime($_POST['pay_date'])) : '';
        $end_free = isset($_POST['end_free']) ? $_POST['end_free'] : '';
        $end_pay = isset($_POST['end_pay']) ? $_POST['end_pay'] : '';
        

        if ($action == 'modify') {

            $add_query = '';
            if ($pay_date != '') {
                $add_query = ", PayYMD = '$pay_date', PayState = 1";
            }

            $query = "
                UPDATE A_BuySiteAPIControl SET
                ChainName      = '$chain_name'
                , Token        = '$token'
                , FreeYMD      = '$free_date'
                , EndFreeMonth = $end_free
                , EndPayMonth  = $end_pay
                , UpdDate      = GETDATE()
                {$add_query}
                WHERE SeqNo = $seq_no
            ";

        } else if ($action == 'write') {
            $query = "INSERT INTO A_BuySiteAPIControl (ChainName, Token, FreeYMD) VALUES ('$chain_name', '$token', CONVERT(CHAR(8), GETDATE(), 112))";
        } else if ($action == 'lock'){
            $use_flag = isset($_POST['use_flag']) ? $_POST['use_flag'] : '';
            $arr = explode('|', $use_flag);
            $seq_no = isset($arr[0]) ? $arr[0] : '';
            $lock = isset($arr[1]) ? $arr[1] : '';

            $query = "UPDATE A_BuySiteAPIControl SET Lock = $lock, UpdDate = GETDATE() WHERE SeqNo = $seq_no";

        } else {
            throw new Exception("등록 실패(1)");
        }

        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if($rowCount == 0) {
            throw new Exception("등록 실패(2)");
        }

        if($action=='lock') {

            $result_array = array (
                'status' => 1
                , 'msg'  => "등록 성공"
            );

            echo parseJson($result_array);

        } else {
            msgback("등록 성공");
        }
        
    } catch(PDOException $e) {
        if($action=='lock') {
            echo parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()));
        } else {
            msgback($e->getMessage());
        }
    } catch(Exception $e) {
        if($action=='lock') {
            echo parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()));
        } else {
            msgback($e->getMessage());
        }
    }



    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }
