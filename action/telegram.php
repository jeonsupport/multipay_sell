
<?php
    
    class Telegram
    {

        //상수 정의
        const BOT_TOKEN = '1936820323:AAGKCrwlqS1VVKIll2BbBCd8AQT2owd7WkA';
        const API_URL = 'https://api.telegram.org/bot'.self::BOT_TOKEN.'/';

        //프로퍼티 정의
        private $_TELEGRAM_CHAT_ID = array(); // $_TELEGRAM_CHAT_ID = array('message_id값');
        private $_TELEGRAM_MESSAGE = '잔액이 부족합니다.';


        public function __construct($arr, $message='') {
            $this->_TELEGRAM_CHAT_ID = $arr;
            $this->_TELEGRAM_MESSAGE = $message;
        }


        public function telegramExecCurlRequest($handle) {
    
            $response = curl_exec($handle);
        
            if ($response === false) {
                $errno = curl_errno($handle);
                $error = curl_error($handle);
                error_log("Curl returned error $errno: $error\n");
                curl_close($handle);
                return false;
            }
        
            $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
            curl_close($handle);
        
            if ($http_code >= 500) {
                // do not wat to DDOS server if something goes wrong
                sleep(10);
                return false;
            } 
            else if ($http_code != 200) {
        
                $response = json_decode($response, true);
        
                error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        
                if ($http_code == 401) {
                    throw new Exception('Invalid access token provided');
                }
        
                return false;
            } 
            else {
        
                $response = json_decode($response, true);
        
                if (isset($response['description'])) {
                    error_log("Request was successfull: {$response['description']}\n");
                }
        
                $response = $response['result'];
            }
        
            return $response;
        }

        public function telegramApiRequest($method, $parameters) {
    
            if (!is_string($method)) {
                error_log("Method name must be a string\n");
                return false;
            }
        
            if (!$parameters) {
                $parameters = array();
            } 
            else if (!is_array($parameters)) {
                error_log("Parameters must be an array\n");
                return false;
            }
        
            foreach ($parameters as $key => &$val) {
                // encoding to JSON array parameters, for example reply_markup
                if (!is_numeric($val) && !is_string($val)) {
                    $val = json_encode($val);
                }
            }
        
            $url = self::API_URL.$method.'?'.http_build_query($parameters);
        
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        
            return $this->telegramExecCurlRequest($handle);
        }

        public function send() {

            // 메시지 발송 부분
            try {

                foreach($this->_TELEGRAM_CHAT_ID AS $_TELEGRAM_CHAT_ID_STR) {
            
                    $_TELEGRAM_QUERY_STR    = array(
                        'chat_id' => $_TELEGRAM_CHAT_ID_STR,
                        'text'    => $this->_TELEGRAM_MESSAGE
                    );
                
                    $this->telegramApiRequest("sendMessage", $_TELEGRAM_QUERY_STR);
                }
    
                //Ajax response
                $resData = Array(
                    'status' => 1,
                    'msg' => 'success'
                );
    
                echo $this->parseJson($resData);

            
            } catch(Exception $e) {
                echo $this->parseJson(array('status' => 0, 'msg' => $e->getMessage()));
            }


        }

        function parseJson($data){
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
        }

    }
