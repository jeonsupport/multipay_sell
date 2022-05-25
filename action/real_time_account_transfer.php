<?php


    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "deposit_action.php";

    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();

    $seq_no = isset($_POST['sendData']) ? $_POST['sendData'] : '';

    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }
        
        if($seq_no=='') { throw new Exception('seq null'); }


        // 입금 내역이 있을 때 qr코드 바로 보여주기
        $query = "SELECT user_name, total_price, bank_name FROM shop_user_purchase_list WHERE seq_no = :seq_no AND res_flag = 0 AND buy_type = 'qr'";
        $statement = $db_access->prepare($query);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $user_name = isset($row['user_name']) ? $row['user_name'] : '';
        $total_price = isset($row['total_price']) ? $row['total_price'] : '';
        $bank_name = isset($row['bank_name']) ? $row['bank_name'] : '';


        // 입금내역 조회
        $query = 
            "
                SELECT seq_no FROM shop_user_deposit_list 
                WHERE reg_date >= DATE_ADD(NOW(), INTERVAL -1 HOUR)
                AND admin_check = 0
                AND bank_name = :bank_name
                AND user_name = :user_name
                AND real_price = :real_price
                AND use_state = 0
                ORDER BY reg_date DESC
                LIMIT 1
            ";
        $statement = $db_access->prepare($query);
        $statement->bindValue(':bank_name', $bank_name);
        $statement->bindValue(':user_name', $user_name);
        $statement->bindValue(':real_price', $total_price);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $rowCount = $statement->rowCount();
        $d_seq_no = isset($row['seq_no']) ? $row['seq_no'] : '';


        if ($rowCount == 1) {
            $deposit = new DepositAction();
            $result = $deposit->action($user_name, $total_price, $bank_name, $seq_no, $d_seq_no);

            if ($result['status'] == 0) {
                throw new Exception("deposit_error"); // 입금 내역 조회 오류
            } 
        }


        ////////////////////////////////////////////////////////////////////////



        $query = "SELECT Token FROM shop_user_purchase_list WHERE seq_no = :seq_no AND res_flag = 2 AND buy_type = 'qr'"; // 2022-01-06 수정
        $statement = $db_access->prepare($query);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $rowCount = $statement->rowCount();

        $token = isset($row['Token']) ? $row['Token'] : 0;

        if($rowCount==0) {
            throw new Exception('입금전');
        }

        // qrcode 이미지 가져오기
        $request_array = array (
            'auth'     => API_AUTH_TOKEN
            , 'action' => 'qrCodeCreate'
            , 'token'  => $token
        );

        $request = post(API_URL, $request_array);
        $result = json_decode($request, true);
        if ($result['status'] == 0) {
            throw new Exception($result['msg']);
        }
 
        $result_array = array(
            'status' => 1,
            'msg' => 'success',
            'token' => $token,
            'img_url' => $result['url']
        );

        parseJson($result_array);

    } catch(Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(PDOException $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function parseJson($data){
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

