<?php


    include_once "../db_connecter.php";
    include_once "../inc.php";

    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();

    $token  = isset($_POST['token'])  ? $_POST['token']  : '';

    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }
        
        if($token=='') { throw new Exception("파라미터 에러"); }


        // 입금완료 -> 구매완료
        $query = "UPDATE shop_user_purchase_list SET res_flag = 1 WHERE token = :token AND res_flag = 2";
        $statement = $db_access->prepare($query);
        $statement->bindValue('token', $token);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            throw new Exception("처리 실패");
        }


        $result_array = array(
            'status' => 1,
            'msg' => 'success'
        );

        return parseJson($result_array);

    } catch(Exception $e) {
        return parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(PDOException $e) {
        return parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function parseJson($data){
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

