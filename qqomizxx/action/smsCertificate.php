<?php


    define("ALLOWED_WELLCOME", true);
    define("____KEY", "IXnRs0eQN315b5rN54rGclUB5w2tRJHweDq3BWvl61TqrXE09UjWlUttZsU3owQw7aBp1bq4o94xWx7dXjtBVqzwhjlKeQ6QMytu");

    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $errMsg = "";
    $authKey   = isset($_POST['authKey']) ? strip_tags($_POST['authKey']) : '';
    $userName  = isset($_POST['userName']) ? trim(strip_tags($_POST['userName'])) : $errMsg .= "userName; ";
    $realPrice = isset($_POST['totalPrice']) ? trim(strip_tags($_POST['totalPrice'])) : $errMsg .= "realPrice; ";
    $bankName  = isset($_POST['bankName']) ? trim(strip_tags($_POST['bankName'])) : $errMsg .= "bankName; ";



    try {

        //step0. authKey 인증
        if ($authKey !== ____KEY) {
            err_log_insert("인증 실패(양식 미일치)");
            
        } else if ($errMsg !== '') {   //step1. 입금 기록 남기기

            if (!empty($_POST)) {
                $query  = "INSERT INTO shop_user_deposit_list (memo, use_state) VALUES (:memo, 1)";
                $field = array (':memo' => $errMsg);
                query_duplicate($field, $query);
            }

            err_log_insert("구매 정보가 없습니다(양식 미일치)");

        } else {
            $query = "INSERT INTO shop_user_deposit_list (bank_name, user_name, real_price) VALUES (:bank_name, :user_name, :real_price)";
            $statement = $db->prepare($query);
            $statement->bindValue(':bank_name', $bankName);
            $statement->bindValue(':user_name', $userName);
            $statement->bindValue(':real_price', intval($realPrice));
            $statement->execute();
            $rowCount = $statement->rowCount();
            $last_insert_id = $db->lastInsertId();

            if ($rowCount <> 1) {
                throw new Exception("입금 정보 남기기 실패");
            }
        }


        //step2. api request data 추출
        $query = 
            "
                SELECT product_no, total_price, total_count, phone_no
                , (SELECT balance FROM shop_balance) AS balance
                FROM shop_user_purchase_list 
                WHERE user_name = :user_name
                AND real_price = :real_price
                AND bank_name = :bank_name
                AND res_flag = 0
                AND token IS NULL
                ORDER BY reg_date DESC
                LIMIT 1
            ";
            
        $statement = $db->prepare($query);
        $statement->bindValue(':user_name', $userName);
        $statement->bindValue(':real_price', intval($realPrice));
        $statement->bindValue(':bank_name', $bankName);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        
        $product_no     = isset($row['product_no'])  ? $row['product_no']  : '';
        $total_price    = isset($row['total_price']) ? $row['total_price'] : '';
        $total_count    = isset($row['total_count']) ? $row['total_count'] : '';
        $before_balance = isset($row['balance'])     ? $row['balance']     : '';
        $phone_no       = isset($row['phone_no'])    ? $row['phone_no']    : '';
        if ($before_balance < $total_price) { err_log_insert("잔액이 부족합니다."); }
        if ($rowCount == 0) { err_log_insert("구매 정보가 없습니다."); }


        //step3. api 호출
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
        // step4. 사용자 데이터 업뎃


        // 트랜잭션 시작
        $db->beginTransaction();


        // 구매 리스트 사용 완료 처리
        $query = 
            "
                UPDATE shop_user_purchase_list
                SET upd_date = now(3), token = :token, res_flag = 2
                WHERE user_name = :user_name
                AND total_price = :total_price
                AND bank_name = :bank_name
                AND res_flag = 0
                AND token IS NULL
                ORDER BY reg_date DESC
                LIMIT 1
            ";
        $statement = $db->prepare($query);
        $statement->bindValue(':token', $token);
        $statement->bindValue(':user_name', $userName);
        $statement->bindValue(':total_price', $total_price);
        $statement->bindValue(':bank_name', $bankName);
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


        // 성공시 입금 상태 업데이트 0 -> 1
        $query = "UPDATE shop_user_deposit_list SET use_state = 1 WHERE seq_no = :seq_no";
        $statement = $db->prepare($query);
        $statement->bindValue(':seq_no', $last_insert_id);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            $db->rollBack();
            err_log_insert("입금 상태 업데이트 실패");
        }


        ////////////////////////////////////////////////////////////////////////////////

        // 성공 시 커밋
        $db->commit();


        echo json_encode(array('status' => 1, 'msg' => 'ok'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    
    } catch(PDOException $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function query_duplicate($field, $query) {
        $db = $GLOBALS['db'];

        $statement = $db->prepare($query);
        $statement->execute($field);
        $rowCount = $statement->rowCount();

        if ($rowCount == 0) {
            throw new Exception('insert or update fail');
        }

        return;
    }    

    function err_log_insert($msg) {
        $query = "INSERT INTO shop_purchase_fail_log (str_param, error_msg) VALUES (:str_param, :error_msg)";
        $field = array (
            ':str_param'   => print_r($_POST, 1)
            , ':error_msg' => $msg
        );

        query_duplicate($field, $query);

        throw new Exception($msg); // 나중에 변경할 것!!!
    }

    



    