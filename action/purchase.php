<?php

    session_start();

    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "smsutil.php";


    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();
    $sms = new SmsUtil();


    $errMsg = "";
    $strPrice = "";
    $strCount = "";
    $timestamp = time();

    $jData  = isset($_POST['sendData']) ? $_POST['sendData'] : "";
    $productNo = isset($jData['productNo']) ? strip_tags($jData['productNo']) : $errMsg .= "productNo; ";
    $payment = isset($jData['payment']) ? strip_tags($jData['payment']) : $errMsg .= "payment; ";
    $amount = isset($jData['amount']) ? strip_tags($jData['amount']) : $errMsg .= "amount; ";
    $phoneNo = isset($jData['phoneNo']) ? trim($jData['phoneNo']) : $errMsg .= "phoneNo; ";
    $userName = isset($jData['userName']) ? trim($jData['userName']) : $errMsg .= "userName; ";
    $totalCount = isset($jData['totalCount']) ? strip_tags($jData['totalCount']) : $errMsg .= "totalCount; ";
    $totalPrice = isset($jData['totalPrice']) ? strip_tags($jData['totalPrice']) : $errMsg .= "totalPrice; ";
    $buy_type = isset($jData['buy_type']) ? strip_tags($jData['buy_type']) : $errMsg .= "buy_type; ";
    $bankName = isset($jData['bankName']) ? strip_tags($jData['bankName']) : $errMsg .= "bankName; ";
    
    $phoneNo = str_replace('-', '', $phoneNo);
    $phoneNo = strip_tags($phoneNo);
    $userName = strip_tags($userName);


    //session 변수
    $sess_product_no  = isset($_SESSION['product_no'])  ? $_SESSION['product_no']  : '';
    $sess_user_name   = isset($_SESSION['user_name'])   ? $_SESSION['user_name']   : '';
    $sess_total_price = isset($_SESSION['total_price']) ? $_SESSION['total_price'] : '';
    $sess_total_count = isset($_SESSION['total_count']) ? $_SESSION['total_count'] : '';
    $sess_phone_no    = isset($_SESSION['phone_no'])    ? $_SESSION['phone_no']    : '';
    $sess_bank_name   = isset($_SESSION['bank_name'])   ? $_SESSION['bank_name']   : '';

 
    try {
        
        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        if(!isset($jData) && $errMsg != "") {
            throw new Exception("잘못된 호출입니다.");
        }

        // 개인 일별 구입한도 설정(전화번호 기준, 기본 100만원)
        $query = "
            SELECT SUM(total_price) AS sum_price FROM shop_user_purchase_list
            WHERE phone_no = :phone_no
            AND ( res_flag = 1 OR res_flag = 0 )
            AND reg_date BETWEEN DATE_ADD(NOW(), INTERVAL -1 DAY) AND NOW()
        ";
        $statement = $db_access->prepare($query);
        $statement->bindValue(':phone_no', $phoneNo);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $sum_price = isset($row['sum_price']) ? $row['sum_price'] : 0;

        if ( LIMIT_PRICE != 0 ) {
            if (intval($sum_price) + intval($totalPrice) > LIMIT_PRICE) { 
                throw new Exception('개인 일별 구입 한도를 초과했습니다.('.number_format(LIMIT_PRICE).'원)'); 
            }
        }
        // ------------------------------------------------------------------------------------------------
        //step0. 잔액 확인
        $query = "SELECT balance FROM shop_balance WHERE use_state = 1";
        $statement = $db_access->prepare($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $balance = isset($row['balance']) ? $row['balance'] : 0;
        if ($totalPrice > $balance) {
            throw new Exception('잔액이 부족합니다.');
        }


        //step1. 입금 현황 패스워드 생성
        $pwd = sprintf('%04s',rand(1000,9999));
        $dbPWD = password_hash($pwd, PASSWORD_DEFAULT);
      

        //step2. 권종 추출
        $amountArr = explode("|", $amount);
        foreach($amountArr as $value) {
            list($count, $price) = explode(",", $value);
            if($count!=0) {
                $strCount .= $count.",";
                $strPrice .= $price.",";
            }
        }
        $strPrice = substr($strPrice, 0, -1);
        $strCount = substr($strCount, 0, -1);
        
        
        //step3. 사용자 등록 확인
        $query = "SELECT bank_name, seq_no FROM shop_user_purchase_list WHERE user_name = :user_name AND total_price = :total_price AND bank_name = :bank_name AND res_flag = 0 ORDER BY reg_date DESC LIMIT 1";
        $statement = $db_access->prepare($query);
        $statement->bindValue(':user_name', $userName);
        $statement->bindValue(':total_price', $totalPrice);
        $statement->bindValue(':bank_name', $bankName);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $seq_no = isset($row['seq_no']) ? $row['seq_no'] : '';

        if ($rowCount <> 0) {

            if ($sess_product_no     == $productNo 
                && $sess_user_name   == $userName 
                && $sess_total_price == $totalPrice
                && $sess_total_count == $totalCount 
                && $sess_phone_no    == $phoneNo
                && $sess_bank_name   == $bankName
            ) {

            } else {
                throw new Exception("중복된 이름의 구매자가 있습니다. 다음에 다시 시도하십시오.");
            }
            
        }
       
        
        //step4. 입금은행, 계좌 추출
        $query = "SELECT account, bank_name FROM shop_admin_account_list WHERE bank_name = :bank_name";
        $statement = $db_access->prepare($query);
        $statement->bindValue('bank_name', $bankName);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $account = isset($row['account']) ? $row['account'] : '';
        $bankname = $bankName;


        if ($rowCount == 0) {
            throw new Exception("계좌 추출 실패");
        }


        //step5. 상품권 정보 가져오기(상품권명, 수수료율)
        $arrProductInfo = getProductInfo($productNo);
        $productName = $arrProductInfo['product_name'];
        $discount_comm_price = $totalPrice * ($arrProductInfo['discount_comm_rate'] * 0.01);
        $publisher_comm_price = $totalPrice * ($arrProductInfo['publisher_comm_rate'] * 0.01);
        $real_price = $totalPrice - $discount_comm_price;
        $profit_price = $real_price - $publisher_comm_price;
        


        if ($sess_product_no == $productNo 
        && $sess_user_name   == $userName 
        && $sess_total_price == $totalPrice
        && $sess_total_count == $totalCount 
        && $sess_phone_no    == $phoneNo
        && $sess_bank_name   == $bankName
        && $seq_no != ''
        )  {

            $query = "UPDATE shop_user_purchase_list SET reg_date = now(3), pwd = :pwd WHERE seq_no = :seq_no";
            $statement = $db_access->prepare($query);
            $statement->bindValue(':seq_no', $seq_no);
            $statement->bindValue(':pwd', $dbPWD);
            $statement->execute();
            $rowCount = $statement->rowCount();

            if ($rowCount <> 1) {
                throw new Exception("신청 갱신 실패");
            }

        } else {
            //step6. 사용자 등록 정보 입력
            $query = 
                    "
                        INSERT INTO shop_user_purchase_list (product_no
                                                    , chain_price
                                                    , chain_count
                                                    , phone_no
                                                    , user_name
                                                    , total_count
                                                    , total_price
                                                    , discount_comm_price
                                                    , real_price
                                                    , publisher_comm_price
                                                    , profit_price
                                                    , bank_name
                                                    , product_name
                                                    , pwd
                                                    , buy_type

                                                ) VALUES ( :product_no
                                                    , :chain_price
                                                    , :chain_count
                                                    , :phone_no
                                                    , :user_name
                                                    , :total_count
                                                    , :total_price
                                                    , :discount_comm_price
                                                    , :real_price
                                                    , :publisher_comm_price
                                                    , :profit_price
                                                    , :bank_name
                                                    , :product_name
                                                    , :pwd
                                                    , :buy_type
                                                )
                    ";
            $statement = $db_access->prepare($query);
            $statement->bindValue(':product_no', $productNo);
            $statement->bindValue(':chain_price', $strPrice);
            $statement->bindValue(':chain_count', $strCount);
            $statement->bindValue(':phone_no', $phoneNo);
            $statement->bindValue(':user_name', $userName);
            $statement->bindValue(':total_count', $totalCount);
            $statement->bindValue(':total_price', $totalPrice);
            $statement->bindValue(':discount_comm_price', $discount_comm_price);
            $statement->bindValue(':real_price', $real_price);
            $statement->bindValue(':publisher_comm_price', $publisher_comm_price);
            $statement->bindValue(':profit_price', $profit_price);
            $statement->bindValue(':bank_name', $bankname);
            $statement->bindValue(':product_name', $productName);
            $statement->bindValue(':pwd', $dbPWD);
            $statement->bindValue(':buy_type', $buy_type);
            $statement->execute();
            $seq_no = $db_access->lastInsertId();
            $rowCount = $statement->rowCount();

            if ($rowCount <> 1) {
                throw new Exception("사용자 정보 등록 실패");
            }

        }
        

        $result_array = array(
            'status'     => 1,
            'account'    => $account,
            'bankname'   => $bankname,
            'username'   => $userName,
            'totalprice' => $real_price,
            'seq_no'     => $seq_no,
            'pwd'        => $pwd
        );

        // session 남기기
        $_SESSION['product_no']  = $productNo;
        $_SESSION['user_name']   = $userName;
        $_SESSION['total_price'] = $totalPrice;
        $_SESSION['total_count'] = $totalCount;
        $_SESSION['phone_no']    = $phoneNo;
        $_SESSION['bank_name']   = $bankName;

        // sms
        // if($buy_type == 'qr') {
        //     $smsText = "[바우처 이지 패스] \r\n";
        //     $smsText .= "실시간 입금현황 비밀번호 : ".$pwd;
        //     $sms->sendSmsMember($smsText, $phoneNo);
        // }
        
        echo parseJson($result_array);
    
    } catch(Exception $e) {
        echo parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function getProductInfo($productNo) {
        try {
            $db = $GLOBALS['db_access'];

            $query = "SELECT product_name, discount_comm_rate, publisher_comm_rate FROM shop_product_list WHERE product_no = :product_no";
            $statement = $db->prepare($query);
            $statement->bindValue(':product_no', $productNo);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $rowCount = $statement->rowCount();

            $product_name = isset($row['product_name']) ? $row['product_name'] : '';
            $discount_comm_rate = isset($row['discount_comm_rate']) ? $row['discount_comm_rate'] : 0;
            $publisher_comm_rate = isset($row['publisher_comm_rate']) ? $row['publisher_comm_rate'] : 0;

            if($rowCount == 0) {
                throw new Exception("상품권 정보 가져오기 실패");
            }
            
            $return_array = array(
                'product_name' => $product_name,
                'discount_comm_rate' => $discount_comm_rate,
                'publisher_comm_rate' => $publisher_comm_rate
            );

            return $return_array;

        } catch(PDOException $e) {
            throw new Exception($e->getMessage());
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    


    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }


    