<?php


    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

  
    try {

        if(empty($_POST)) {
            throw new Exception('잘못된 호출입니다.');
        }

        $action = isset($_POST['action']) ? $_POST['action'] : '';
        switch($action) {
            case 'change_management':
                echo upManagement();
                break;
            default:
                throw new Exception('Wrong HTTP request');
                break;
                
        }


    } catch(PDOException $e) {
        $db->rollBack();
        msgback($e->getMessage());
    } catch(Exception $e) {
        msgback($e->getMessage());
    }



    function upManagement() {
        
        $db = $GLOBALS['db'];

        //트랜잭션 시작
        $db->beginTransaction();

        $seq_no = isset($_POST['seq_no']) ? $_POST['seq_no'] : '';
        $product_no = isset($_POST['product_no']) ? $_POST['product_no'] : '';
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        $discount_comm_rate = isset($_POST['discount_comm_rate']) ? $_POST['discount_comm_rate'] : '';
        $publisher_comm_rate = isset($_POST['publisher_comm_rate']) ? $_POST['publisher_comm_rate'] : '';

        $query = "UPDATE shop_product_list SET product_name = '$product_name', discount_comm_rate = $discount_comm_rate, publisher_comm_rate = $publisher_comm_rate WHERE seq_no = $seq_no";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        // if($rowCount == 0) {
        //     $db->rollBack();
        //     throw new Exception($query);
        // }

        $p1000 = isset($_POST['p1000']) ? $_POST['p1000'] : '';
        $p3000 = isset($_POST['p3000']) ? $_POST['p3000'] : '';
        $p5000 = isset($_POST['p5000']) ? $_POST['p5000'] : '';
        $p10000 = isset($_POST['p10000']) ? $_POST['p10000'] : '';
        $p30000 = isset($_POST['p30000']) ? $_POST['p30000'] : '';
        $p50000 = isset($_POST['p50000']) ? $_POST['p50000'] : '';

        $query = "
            UPDATE shop_product_use_list SET 
            use_flag = CASE price WHEN 1000  THEN $p1000 ELSE use_flag END,
            use_flag = CASE price WHEN 3000  THEN $p3000 ELSE use_flag  END,
            use_flag = CASE price WHEN 5000  THEN $p5000 ELSE use_flag  END,
            use_flag = CASE price WHEN 10000 THEN $p10000 ELSE use_flag  END,
            use_flag = CASE price WHEN 30000 THEN $p30000 ELSE use_flag  END,
            use_flag = CASE price WHEN 50000 THEN $p50000 ELSE use_flag  END
            WHERE price in (1000,3000,5000,10000,30000,50000)
            AND product_no = $product_no
        ";

        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();


        // if($rowCount == 0) {
        //     $db->rollBack();
        //     throw new Exception($query);
        // }


        $db->commit();

        msgback("변경되었습니다.");
        popParentReload();
    }

    function parseJson($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }
