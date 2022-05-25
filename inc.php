<?php

    // 실장비
    // const QR_PURCHASE_URL  = 'http://vepass.co.kr/qqomizxx/action/qrPurchase.php';
    // const SMS_PURCHASE_URL = 'http://vepass.co.kr/qqomizxx/action/smsPurchase.php';
    // const API_URL = "http://15.165.74.236/api/purchaseSiteController.php";


    // 로컬 테스트 용
    const QR_PURCHASE_URL  = "http://192.168.0.14/wellcommad/qqomizxx/action/qrPurchase.php";
    const SMS_PURCHASE_URL = "http://192.168.0.14/wellcommad/qqomizxx/action/smsPurchase.php";
    const API_URL          = "http://192.168.0.14/test_api/purchaseSiteController.php";


    const API_AUTH_TOKEN = 'ABCD'; // api 인증 토큰
    const LIMIT_PRICE = 1000000; // 개인 일별 구입한도 설정 (기본 100만원), 0일때 무한


    function movepage($url, $msg=""){
        echo "<script>";
        if($msg){
            echo "alert(\"{$msg}\");";
        }
        if(is_numeric($url)) echo "history.go($url);";
        else echo "location.replace(\"$url\");";
        echo "opener.parent.location.reload();";
        echo "</script>";
        exit;
    }


    function msgback($msg){
        movepage(-1, $msg);
    }


    function popClose($msg) {
        echo "<script>";
        if($msg){
            echo "alert(\"{$msg}\");";
        }
        echo "self.close();";
        echo "</script>";
        exit;
    }

    function post($url, $fields) {

        $post_field_string = http_build_query($fields, '', '&');
        $ch = curl_init(); // curl 초기화
      
        curl_setopt($ch, CURLOPT_URL, $url);                          
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);              
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);           
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);    
        curl_setopt($ch, CURLOPT_POST, true);                          
      
        $response = curl_exec($ch);
        curl_close ($ch);
      
        return $response;
      
      }
      
      function ssl_post($url, $fields) {
        $post_field_string = http_build_query($fields, '', '&');
      
        $ch = curl_init(); // curl 초기화
        curl_setopt($ch, CURLOPT_URL, $url); 
      
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
      
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string); 
      
        $response = curl_exec($ch);
        curl_close ($ch);
      
        return $response;
        
      }

      function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
      }


      function isHttpsRequest() {	

        if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443) {		
            return 'https'; 
        }
        return 'http';
    }
    function check_referer(){
    
        $protocol = isHttpsRequest().'://';
        $host = $protocol.getenv('HTTP_HOST');
        if($host == substr(getenv('HTTP_REFERER'),0,strlen($host)))
            return 1;
        else
            return 0;
    }