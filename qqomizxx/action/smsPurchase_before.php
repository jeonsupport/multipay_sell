<?php

    define("ALLOWED_WELLCOME", true);

    include_once "smsutil.php";
    include_once "../../db_connecter.php";

    class SMSPurchase {


        //변수 정의
        protected static $db;
        protected static $sms;
        protected static $cipher;

        public function __construct() {
            self::$db = new MySQL_Connecter();
            self::$db = self::$db->ConnectServer();
            self::$sms = new SmsUtil();
            // self::$cipher = new Cipher();

        }

        public function purchase() {

            try {
              
                $errMsg = "";
                $userName = isset($_POST['userName']) ? strip_tags($_POST['userName']) : $errMsg .= "userName; ";
                $totalPrice = isset($_POST['totalPrice']) ? strip_tags($_POST['totalPrice']) : $errMsg .= "totalPrice; ";
                $bankName = isset($_POST['bankName']) ? strip_tags($_POST['bankName']) : $errMsg .= "bankName; ";

                // 관리자에서 sms 발송시
                $adminSMS = isset($_POST['adminSMS']) ? strip_tags($_POST['adminSMS']) : FALSE;
                $seqNo = isset($_POST['seqNo']) ? strip_tags($_POST['seqNo']) : 0;

   
                if($adminSMS === 'true') {
                    
                    if($adminSMS=="" || $seqNo=="") {
                        throw new Exception("잘못된 호출입니다.(396)");
                    }
                    $adminSMS = TRUE;

                    
                } else {

                    if($errMsg !== "") {
                        throw new Exception("잘못된 호출입니다.(4022)");
                    } else {
                        
                        // 입금정보 등록
                        $query = "INSERT INTO shop_user_deposit_list(bank_name, user_name, real_price) VALUES (:BankName, :UserName, :TotalPrice)";
                        $field = array(
                            ":BankName" => $bankName,
                            ":UserName" => $userName,
                            ":TotalPrice" => $totalPrice
                        );
    
                        self::inUserDepositHis($field, $query);
                        $adminSMS = 0;
                    }

                }


                //구매 프로시저 호출
                $statement = self::$db->prepare("CALL sp_shop_product_purchase('$userName', $totalPrice, '$bankName', $adminSMS, $seqNo, @int_res_no, @str_err_msg, @int_group_no)");
                $statement->execute();

                $query = "SELECT @int_res_no AS sp_result_no, @str_err_msg AS sp_result_msg, @int_group_no AS sp_group_no";
                $statement = self::$db->prepare($query);
                $statement->execute();
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                $rowCount = $statement->rowCount();

                $sp_result_no = isset($row['sp_result_no']) ? $row['sp_result_no'] : '';
                $sp_result_msg = isset($row['sp_result_msg']) ? $row['sp_result_msg'] : '';
                $sp_group_no = isset($row['sp_group_no']) ? $row['sp_group_no'] : '';
                
                if($rowCount == 0) { throw new Exception("프로시저 에러!"); }
                if($sp_result_no != 0) { throw new Exception("sp exception:".$sp_result_msg); }



                //group 번호로 핀번호 추출
                $query = "
                    SELECT A.product_name, A.total_count, A.real_price, A.phone_no, B.serial_code, B.price
                    FROM shop_user_purchase_list AS A
                    JOIN shop_qr_serial_code_list AS B
                    WHERE A.group_no = $sp_group_no
                    AND B.group_no = $sp_group_no
                    AND A.res_flag = 1
                    AND B.use_flag = 2
                ";
                $statement = self::$db->prepare($query);
                $statement->execute();
                $rowCount = $statement->rowCount();

                if($rowCount == 0) { throw new Exception("핀번호 조회 에러"); }

                $second_text = "";
                $pincode = "";
                while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $product_name  = isset($row['product_name']) ? $row['product_name'] : '';
                    $total_count = isset($row['total_count']) ? $row['total_count'] : '';
                    $real_price = isset($row['real_price']) ? $row['real_price'] : '';
                    $phone_no = isset($row['phone_no']) ? $row['phone_no'] : '';
                    
                    $serial_code = isset($row['serial_code']) ? $row['serial_code'] : '';
                    $price = isset($row['price']) ? $row['price'] : '';

                    $priceLen = strlen($price);
                    // 문구 변경
                    $str_price = "";
                    // $productname = substr($productname, 0, 2);
                    if($priceLen==6) {
                        $str_price = substr($price, 0, 2)."만원권";
                    } else if($priceLen==5) {
                        $str_price = substr($price, 0, 1)."만원권";
                    } else {
                        $str_price = substr($price, 0, 1)."천원권";
                    }
                    
                    $second_text .= "\r\n".$serial_code."(".$str_price.")";
                    $pincode .= $serial_code.',';
                }

                $first_text = "[바우처 이지 패스] \r\n";
                $first_text .= $product_name." / 총".number_format($real_price)."원, ".$total_count."장";
                $sms_text = $first_text.$second_text;

                //sms
                $sms = self::$sms->sendSmsMember($sms_text, $phone_no);
                $pincode = substr($pincode, 0, -1);
                $result_array = array(
                    'status' => '1',
                    'msg' => $sms
                );

                echo self::parseJson($result_array);
            
            
            } catch(ErrorException $e) {
                $error_msg = $e->getMessage();
                $str_param = print_r($_POST, 1);
                $query = "INSERT INTO shop_purchase_fail_log (str_param, error_msg) VALUES ('$str_param','$error_msg')";
                self::insert($query);
                
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } catch(Exception $e) {
                $error_msg = $e->getMessage();
                $str_param = print_r($_POST, 1);
                $query = "INSERT INTO shop_purchase_fail_log (str_param, error_msg) VALUES ('$str_param','$error_msg')";
                self::insert($query);
                
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }



        public function inUserDepositHis($field, $query) {
            try {

                $statement = self::$db->prepare($query);
                $statement->execute($field);
                $rowCount = $statement->rowCount();

                if($rowCount == 0) {
                    throw new Exception("입급정보 남기기 실패(307)");
                }

                return;

            } catch(PDOException $e) {
                throw new Exception("Database query error : inUserDepositHis");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function parseJson($data){
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
        }

        public function insert($query)  {

            $statement = self::$db->prepare($query);
            $statement->execute();

            return;            
        }


        
    }


    $realPurchase = new SMSPurchase();
    $realPurchase->purchase();
