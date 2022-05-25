<?php

    session_start();

    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        // 파라미터 처리
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        if ($token == '') { throw new Exception("업로드할 QR핀코드를 입력해주세요."); }

        // 현재 잔액 추출
        $query = "SELECT balance FROM shop_balance WHERE use_state = 1";
        $statement = $db->query($query);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $rowCount = $statement->rowCount();
        $balance = isset($row['balance']) ? $row['balance'] : 0;

        if ($rowCount == 0) {
            throw new Exception("db error");
        }


        // api 전달
        $arr_data = array (
            'action'     => 'qrPinUpload'
            , 'auth'     => API_AUTH_TOKEN
            , 'pin_code' => $token
        );

        $res_data = post(API_URL, $arr_data);
        $result = json_decode($res_data, true);

        // 실패
        if ($result['status'] == 0) {
            err_log_insert("API ERROR : ".$result['msg']);
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // 트랜잭션 시작
        $db->beginTransaction();


        // 성공 (QR핀코드 등록, 잔액 갱신)
        // QR핀코드 등록
        $query = 
            "
                INSERT INTO shop_qr_serial_code_list (serial_code, product_no, product_name, before_price, total_price, total_count, admin_ip, admin_id)
                VALUES (:serial_code, :product_no, :product_name, :before_price, :total_price, :total_count, :admin_ip, :admin_id)
            ";
        $field = array (
            ':serial_code'    => $token
            , ':product_no'   => $result['data']['product_no']
            , ':product_name' => $result['data']['product_name']
            , ':before_price' => $balance
            , ':total_price'  => $result['data']['total_price']
            , ':total_count'  => $result['data']['total_count']
            , ':admin_ip'     => $_SESSION['AdminID']
            , ':admin_id'     => get_client_ip()
        );
        $statement = $db->prepare($query);
        $statement->execute($field);
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            $db->rollBack();
            err_log_insert("QR핀코드 등록 실패");
        }


        //잔액 갱신
        $query = "UPDATE shop_balance SET balance = :balance WHERE use_state = 1";
        $statement = $db->prepare($query);
        $statement->bindValue(':balance', $result['balance']);
        $statement->execute();
        $rowCount = $statement->rowCount();

        // if ($rowCount <> 1) {
        //     $db->rollBack();
        //     err_log_insert("잔액 갱신 실패");
        // }


        //잔액 로그
        $query = "INSERT INTO shop_balance_log (buyer_phone_no, before_balance, buy_price, return_balance) VALUES (:buyer_phone_no, :before_balance, :buy_price, :return_balance)";
        $statement = $db->prepare($query);
        $statement->bindValue(':buyer_phone_no', '9999');
        $statement->bindValue(':before_balance', $balance);
        $statement->bindValue(':buy_price', $result['data']['total_price']);
        $statement->bindValue(':return_balance', $result['balance']);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount <> 1) {
            $db->rollBack();
            err_log_insert("잔액 로그 입력 실패");
        }
        

        $db->commit();

        msgback("업로드를 완료하였습니다.");


    } catch (PDOException $e) {
        msgback($e->getMessage());
    } catch (Exception $e) {
        msgback($e->getMessage());
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