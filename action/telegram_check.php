
<?php
    
    include_once "telegram.php";
    include_once "../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    try {

        // 잔액 체크 (텔레그램 전송)
        $query = "
            SELECT a.telegram_id, b.balance, b.alam_money FROM shop_admin_member a
            LEFT JOIN shop_balance b
            ON a.authority = b.alam_group
            WHERE b.use_state = 1
            AND b.balance <= b.alam_money
        ";
        $statement = $db->query($query);
        $rowCount = $statement->rowCount();
        if ($rowCount == 0) {
            throw new Exception("tg spare");
        }

        $telegram_id_arr = array();
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $balance = isset($row['balance']) && !empty($row['balance']) ? $row['balance'] : '';
            $telegram_id = isset($row['telegram_id']) && !empty($row['telegram_id']) ? $row['telegram_id'] : '';

            if ($telegram_id != '') {
                $telegram_id_arr[] = $telegram_id;
            }
        }

        if (!empty($telegram_id_arr)) {
            $tg_message = 'QR핀코드 잔액 충전이 필요합니다 ::: 현재잔액('.number_format($balance).'원)';
            $tg = new Telegram($telegram_id_arr, $tg_message);
            $tg->send();
        } else {
            throw new Exception('tg check error');
        }

    } catch(PDOException $e) {
        echo parseJson(array('status' => 0, 'msg' => 'tg check db error'));
    } catch(Exception $e) {
        echo parseJson(array('status' => 0, 'msg' => $e->getMessage()));
    }
    

    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }
