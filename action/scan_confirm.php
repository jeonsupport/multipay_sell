<?php


    include_once "../inc.php";

    $token = isset($_POST['token']) ? $_POST['token'] : '';

    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        if($token=='' || empty($_POST)) { throw new Exception('param null'); }


        // qrcode 스캔 검증
        $request_array = array (
            'action'  => 'scan'
            , 'auth'     => API_AUTH_TOKEN
            , 'token'  => $token
        );

        $request = post(API_URL, $request_array);
        $result = json_decode($request, true);
        if ($result['status'] == 0) {
            throw new Exception($result['msg']);
        }
 
        $result_array = array(
            'status' => 1,
            'msg' => 'success'
        );

        return parseJson($result_array);

    } catch(Exception $e) {
        return parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch(PDOException $e) {
        return parseJson(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function parseJson($data){
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }

