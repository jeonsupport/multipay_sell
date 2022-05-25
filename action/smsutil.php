<?php


    class SmsUtil {

        protected static $db;
        const SECRET_KEY = '6MwpNi19Hu7UCp0C';
        const SEND_NUMBER = '16667399';
        const MESSAGE_TITLE = '(주)바우처 이지 패스';
        


        /* form 양식으로 전송할 때 */
        // public function file_post_contents($url, $data, $username = null, $password = null) {

        //     $postdata = $data;

        //     $opts = array('http' =>
        //         array(
        //             'method'  => 'POST',
        //             'header'  => 'Content-type: application/x-www-form-urlencoded',
        //             'content' => $postdata
        //         )
        //     );

        //     if($username && $password)
        //     {
        //         $opts['http']['header'] = ("Authorization: Basic " . base64_encode("$username:$password"));
        //     }

        //     $context = stream_context_create($opts);
        //     return file_get_contents($url, false, $context);

        // }

        public function post_contents($url, $post_data) {

            try {
                $ch = curl_init();
                $headers = array('Content-Type:application/json;charset=UTF-8');

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_URL, $url);
                
                //header값 셋팅(없을시 삭제해도 무방함) 
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                //POST방식
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POST, true);

                //POST방식으로 넘길 데이터(JSON 데이터)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                
                $response = curl_exec($ch);

                if(curl_error($ch)) {
                    throw new Exception('curl error');
                } else {
                    $curl_data = $response;
                }

                curl_close($ch);

                return $response;

            } catch(Exception $e) {
                throw new Exception($e);
            }

        }
            
        
        public function sendSmsMember($message, $phone) {

            
            $intRetVal = 0;
            $length = strlen($message); // 메세지 길이

            $hostNameUrl = 'https://api-sms.cloud.toast.com'; 
            $requestUrl = '/sms/v2.4/appKeys/'.self::SECRET_KEY;
            $smsUrl = '/sender/sms';
            $mmsUrl = '/sender/mms';

            $sendNo = self::SEND_NUMBER; // 발신번호
            $recipientNo = str_replace('-', '', $phone); // 수신번호
            $title = self::MESSAGE_TITLE; // 타이틀
            $msg = $message; // 본문
            $apiUrl = ''; //hostNameUrl + requestUrl;

            $jsonData = array();
            $jData = array(
                'recipientNo' => $recipientNo
            );

            array_push($jsonData, $jData);

            $result_array = array(
                'body' => $msg,
                'sendNo' => $sendNo,
                'recipientList' => $jsonData
            );


            //JSON을 활용한 body data 생성
            if($length <= 90) { //sms
                $apiUrl = $hostNameUrl.$requestUrl.$smsUrl;
            } else { // mms일때는 title 추가
                $apiUrl = $hostNameUrl.$requestUrl.$mmsUrl;
                $result_array['title'] = $title;
            }

    
            try {
                $sendData = json_encode($result_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $sendData = str_replace('\u0000', '', $sendData);
    

                $response_data = self::post_contents($apiUrl, $sendData);
                $data = json_decode($response_data);
            
                
                $resultCode    = $data->header->resultCode;
                $resultMessage = $data->header->resultMessage;
                $isSuccessful  = $data->header->isSuccessful;
                $errorMsg = "";


                if($isSuccessful) {
                    return $resultMessage;
                } else {
                    $errorMsg .= "code : ".$resultCode;
                    $errorMsg .= " message : ".$resultMessage;
        
                    throw new Exception("SMS Exception ///".$errorMsg);
                }
                

            } catch(ErrorException $e) {
                echo $e->getMessage();
            } catch(Exception $e) {
                echo $e->getMessage();
            }
            
        }
    }


?>

