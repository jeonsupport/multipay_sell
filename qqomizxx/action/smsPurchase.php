<?php


    include_once "smsutil.php";
    include_once "../../db_connecter.php";

    class SMSPurchase { // cokoa


        //변수 정의
        protected static $db;
        protected static $sms;
        protected static $cipher;

        public function __construct() {
            self::$db = new MySQL_Connecter();
            self::$db = self::$db->ConnectServer();
            self::$sms = new SmsUtil();

        }

        public function purchase() {

            try {
              
                $json = $_POST['json'];
                $arr = json_decode($json, true);

                $productname = isset($arr['productname']) ? $arr['productname'] : '';
                $hap = isset($arr['hap']) ? $arr['hap'] : '';
                $phoneno = isset($arr['phoneno']) ? $arr['phoneno'] : '';
                $arr_data = isset($arr['data']) ? $arr['data'] : '';

                $new_array = array();
                $second_text = '';
                $pincode = '';
                for($i=0; $i<sizeof($arr_data); $i++) {
                    $new_array[$i] = $arr_data[$i];
                }

                $rcnt = 0;
                foreach($new_array as $v) {
                    $rcnt++;
                    $serial_code = $v['pincode'];
                    $price = $v['price'];

                    $priceLen = strlen($price);
                    // 문구 변경
                    $str_price = "";
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
                $first_text .= $productname." / 총 : ".number_format($hap)."원, ".$rcnt."개";
                $sms_text = $first_text.$second_text;

                
                //sms
                $sms = self::$sms->sendSmsMember($sms_text, $phoneno);
                $result_array = array(
                    'status' => '1',
                    'msg' => $sms
                );

                //전송 로그 남기기
                $query = "INSERT INTO shop_sms_message_log (phone_no, message) VALUES ('$phoneno', '$sms_text')";
                $statement = self::$db->prepare($query);
                $statement->execute();

                echo self::parseJson($result_array);
            
            
            } catch(ErrorException $e) {

                
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } catch(Exception $e) {

                
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }



        public function parseJson($data){
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
        }

 
        
    }


    $realPurchase = new SMSPurchase();
    $realPurchase->purchase();
