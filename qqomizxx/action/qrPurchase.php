<?php

    define("ALLOWED_WELLCOME", true);

    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();


    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }
        
        // 파라미터 받기
        $seq_no = isset($_POST['seqNo']) ? strip_tags($_POST['seqNo']) : '';
        if ($seq_no == '') { throw new Exception("잘못된 호출입니다."); }


        // step1. 입금정보 추출
        $query = "SELECT product_no, user_name, total_price, total_count, real_price, bank_name, phone_no from shop_user_purchase_list WHERE res_flag = 2 AND seq_no = :seq_no"; // 입금완료 상태에서만
        $statement = $db->prepare($query);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $product_no  = isset($row['product_no'])  ? $row['product_no']  : '';
        $user_name   = isset($row['user_name'])   ? $row['user_name']   : '';
        $total_price = isset($row['total_price']) ? $row['total_price'] : '';
        $total_count = isset($row['total_count']) ? $row['total_count'] : '';
        $real_price  = isset($row['real_price'])  ? $row['real_price']  : '';
        $bank_name   = isset($row['bank_name'])   ? $row['bank_name']   : '';
        $phone_no    = isset($row['phone_no'])    ? $row['phone_no']    : '';

        if ($rowCount == 0) { throw new Exception("잘못된 호출입니다."); }


        // step2. 잔액 확인
        $query = "SELECT balance FROM shop_balance WHERE use_state = 1";
        $statement = $db->query($query);
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $before_balance = isset($row['balance']) ? $row['balance'] : 0;
        if ($total_price > $before_balance) { throw new Exception("잔액이 부족합니다."); }
        if ($rowCount == 0) { throw new Exception("구매 정보가 없습니다."); }


        // step3. 관리자 변경시 입금정보 남기기
        $query = "INSERT INTO shop_user_deposit_list (bank_name, user_name, real_price, admin_check, use_state) VALUES (:bank_name, :user_name, :real_price, 1, 1)";
        $field = array (
            ':bank_name'    => $bank_name
            , ':user_name'  => $user_name
            , ':real_price' => intval($real_price)
        );
        $statement = $db->prepare($query);
        $statement->execute($field);
        $rowCount = $statement->rowCount();

        if ($rowCount == 0) { throw new Exception("입금기록 등록 실패"); }


        // step4. api 호출
        $arr_data = array (
            'action'          => 'qr_update'
            , 'auth'          => API_AUTH_TOKEN
            , 'product_no'    => intval($product_no)
            , 'total_price'   => intval($total_price)
            , 'total_count'   => intval($total_count)
        );
        $json_result = post(API_URL, $arr_data);
        $arr_result = json_decode($json_result, true);
        
        if ($arr_result['status'] == 0) {
            err_log_insert($arr_result['msg']);
        }
        $status  = isset($arr_result['status'])  ? $arr_result['status']  : '';
        $msg     = isset($arr_result['msg'])     ? $arr_result['msg']     : '';
        $balance = isset($arr_result['balance']) ? $arr_result['balance'] : '';
        $token   = isset($arr_result['token'])   ? $arr_result['token']   : '';



        ////////////////////////////////////////////////////////////////////////////////
        //step5. 사용자 데이터 업뎃

        
        // 트랜잭션 시작
        $db->beginTransaction();


        // 구매 리스트 사용 완료 처리
        $query = 
            "
                UPDATE shop_user_purchase_list
                SET upd_date = now(3), token = :token
                WHERE user_name = :user_name
                AND total_price = :total_price
                AND bank_name = :bank_name
                AND res_flag = 2
                AND seq_no = :seq_no
            ";
        $statement = $db->prepare($query);
        $statement->bindValue(':token', $token);
        $statement->bindValue(':user_name', $user_name);
        $statement->bindValue(':total_price', $total_price);
        $statement->bindValue(':bank_name', $bank_name);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            $db->rollBack();
            err_log_insert("구매 리스트 사용 완료 처리 실패");
        }


        // 잔액 업데이트
        $query = "UPDATE shop_balance SET balance = :balance";
        $statement = $db->prepare($query);
        $statement->bindValue(':balance', $balance);
        $statement->execute();
        $rowCount = $statement->rowCount();

        // if ($rowCount <> 1) {
        //     $db->rollBack();
        //     err_log_insert("잔액 업데이트 실패");
        // }


        // 잔액 로그 업데이트
        $query = 
            "
                INSERT INTO shop_balance_log (buyer_phone_no, before_balance, buy_price, return_balance)
                VALUES (:buyer_phone_no, :before_balance, :buy_price, :return_balance)
            ";
        $statement = $db->prepare($query);
        $statement->bindValue(':buyer_phone_no', $phone_no);
        $statement->bindValue(':before_balance', $before_balance);
        $statement->bindValue(':buy_price', $total_price);
        $statement->bindValue(':return_balance', $balance);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            $db->rollBack();
            err_log_insert("잔액 로그 업데이트 실패");
        }

        ////////////////////////////////////////////////////////////////////////////////


        // 성공 시 커밋
        $db->commit();


        echo json_encode(array('status' => 1, 'msg' => 'ok'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        

    } catch (PDOException $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    

    function err_log_insert($msg) {
        $db = $GLOBALS['db'];

        $query = "INSERT INTO shop_purchase_fail_log (str_param, error_msg) VALUES (:str_param, :error_msg)";
        $field = array (
            ':str_param'   => print_r($_POST, 1)
            , ':error_msg' => $msg
        );
        $statement = $db->prepare($query);
        $statement->execute($field);
        $rowCount = $statement->rowCount();

        if ($rowCount == 0) {
            throw new Exception($msg.', 에러메세지 등록 실패');
        }

        throw new Exception($msg); // 나중에 변경할 것!!!
    }


