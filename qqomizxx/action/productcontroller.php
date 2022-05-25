<?php


    include_once "../../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

  
    try {


        if(empty($_POST)) {
            throw new Exception('잘못된 호출입니다.');
        }

        $action = isset($_POST['action']) ? $_POST['action'] : '';
        switch($action) {
            case 'web_view':
                echo upWebState($_POST['use_flag']);
                break;
            default:
                throw new Exception('Wrong HTTP request');
                break;
                
        }


    } catch(PDOException $e) {
        echo parseJson(array('status' => 0, 'message' => 'fail', 'data' => $e->getMessage()));
    } catch(Exception $e) {
        echo parseJson(array('status' => 0, 'message' => 'fail', 'data' => $e->getMessage()));
    }

    function upWebState($state) {

        if(!isset($state) && empty($state)) {
            throw new Exception("param not found : upWebState");
        }

        $db = $GLOBALS['db'];
        $arr = explode('|', $state);
        $seq_no = isset($arr[0]) ? $arr[0] : '';
        $use_flag = isset($arr[1]) ? $arr[1] : '';

        $query = "UPDATE shop_product_list SET use_flag = $use_flag, upd_date = NOW(3) WHERE seq_no = {$seq_no}";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if($rowCount==0) {
            throw new Exception('업데이트 실패');
        }

        $result_array = array(
            'status' => 1,
            'message' => '변경 완료되었습니다.'
        );

        return parseJson($result_array);

    }


    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }
