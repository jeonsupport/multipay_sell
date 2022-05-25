<?php

    include_once "../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();

    $now = time();
    // $min_time = 86400; // 하루(sec)
    $min_time = 9999999999999999999999999;
    $timeout = false;

    $jData  = isset($_POST['authData']) ? $_POST['authData'] : "";
    $userAuth = isset($jData['userAuth']) ? $jData['userAuth'] : false; 
    $phoneNo = isset($jData['phoneNo']) ? trim($jData['phoneNo']) : $errMsg .= "phoneNo; ";
    $phoneNo = str_replace('-', '', $phoneNo);
    $phoneNo = strip_tags($phoneNo);


    try {

        // ------------------------------------------------------------------------------------------
        // 사용자 본인인증
        if($userAuth == true) {
            // $query = "
            //     IF NOT EXISTS (SELECT TOP 1 * FROM shop_danal_auth WHERE phone_no = '$phoneNo') BEGIN
            //     INSERT INTO shop_danal_auth(phone_no) VALUES('$phoneNo')
            //     END
            //     SELECT TOP 1 * FROM shop_danal_auth WHERE phone_no = '$phoneNo';
            // ";
            $query1 = "
                INSERT INTO shop_danal_auth (phone_no)
                SELECT * FROM (SELECT '$phoneNo' AS phone_no) AS TMP
                WHERE NOT EXISTS (SELECT 1 FROM shop_danal_auth WHERE phone_no = '$phoneNo');
            ";
            $statement = $db_access->prepare($query1);
            $statement->execute();


            //조회
            $query2 = "SELECT seq_no, time_stamp FROM shop_danal_auth WHERE phone_no = '$phoneNo'";
            $statement = $db_access->prepare($query2);
            $statement->execute();
            $row=$statement->fetch(PDO::FETCH_ASSOC);
            $rowCount = $statement->rowCount();

            $time_stamp = isset($row['time_stamp']) ? $row['time_stamp'] : "";
            $seq_no = isset($row['seq_no']) ? $row['seq_no'] : "";

            if($rowCount==0) {
                throw new Exception("인증 실패");
            }


            if($time_stamp >= $now - $min_time && $time_stamp != "") {

            } else {
                $timeout = true; // 시간초과
            }

            $result_array = array(
                'status' => 1,
                'timeout' => $timeout,
                'seqno' => $seq_no
            );

            echo parseJson($result_array);
            return;
        }


    } catch(Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(PDOException $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

