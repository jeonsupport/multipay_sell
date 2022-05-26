<?php

    include_once "db_connecter.php";
    include_once "paging.php";
    include_once "inc.php";


    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();


    //param
    $err_msg = '';
    $product_no = isset($_GET['product'])   ? $_GET['product']   : $err_msg .= 'product_no; ';
    $amount     = isset($_GET['amount'])    ? $_GET['amount']    : $err_msg .= 'amount; ';
    $price      = isset($_GET['price'])     ? $_GET['price']     : $err_msg .= 'price; ';
    $bank_name  = isset($_GET['bank_name']) ? $_GET['bank_name'] : $err_msg .= 'bank_name; ';
    $name       = isset($_GET['name'])      ? $_GET['name']      : $err_msg .= 'name; ';
    $phone_no   = isset($_GET['phone_no'])  ? $_GET['phone_no']  : $err_msg .= 'phone_no; ';
    $page       = isset($_GET['page'])      ? $_GET['page']      : $err_msg .= 'page; ';
    $seq_no     = isset($_GET['seq_no'])    ? $_GET['seq_no']    : $err_msg .= 'seq_no; ';
    $back_url = urldecode($_SERVER['QUERY_STRING']);


    if ($err_msg != '') {
        movepage("process.php","잘못된 접근입니다.");
    }


    try {
        $query = 
            "
                SELECT product_name, chain_price, chain_count, user_name, total_count, total_price, discount_comm_price, real_price, token, res_flag 
                FROM shop_user_purchase_list
                WHERE product_no = :product_no
                AND total_count = :total_count
                AND real_price = :real_price
                AND bank_name = :bank_name
                AND user_name = :user_name
                AND phone_no = :phone_no
                AND seq_no = :seq_no
                AND res_flag = 2
                AND token IS NOT NULL
            ";
        $statement = $db->prepare($query);
        $statement->bindValue(':product_no', $product_no);
        $statement->bindValue(':total_count', $amount);
        $statement->bindValue(':real_price', $price);
        $statement->bindValue(':bank_name', $bank_name);
        $statement->bindValue(':user_name', $name);
        $statement->bindValue(':phone_no', $phone_no);
        $statement->bindValue(':seq_no', $seq_no);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $err_msg = "";
        $product_name  = isset($row['product_name']) ? $row['product_name'] : $err_msg .= 'product_name; ';
        $chain_price = isset($row['chain_price']) ? $row['chain_price'] : $err_msg .= 'chain_price; ';
        $chain_count = isset($row['chain_count']) ? $row['chain_count'] : $err_msg .= 'chain_count; ';
        $user_name = isset($row['user_name']) ? $row['user_name'] : $err_msg .= 'user_name; ';
        $total_count = isset($row['total_count']) ? $row['total_count'] : $err_msg .= 'total_count; ';
        $total_price = isset($row['total_price']) ? $row['total_price'] : $err_msg .= 'total_price; ';
        $discount_comm_price = isset($row['discount_comm_price']) ? $row['discount_comm_price'] : $err_msg .= 'discount_comm_price; ';
        $real_price = isset($row['real_price']) ? $row['real_price'] : $err_msg .= 'real_price; ';
        $token = isset($row['token']) ? $row['token'] : $err_msg .= 'token; ';
        $res_flag = isset($row['res_flag']) ? $row['res_flag'] : $err_msg .= 'res_flag; ';

        if ($res_flag <> 2) {
            throw new Exception("");
        } 

        // qrcode 이미지 가져오기
        $request_array = array (
            'auth'     => API_AUTH_TOKEN
            , 'action' => 'qrCodeCreate'
            , 'token'  => $token
        );
        
        $request = post(API_URL, $request_array);
        $result = json_decode($request, true);
        $qr_image_url = "";
        if ($result['status'] == 1) {
            $qr_image_url = $result['url'];
        }


    } catch(Exception $e) {
        if ($e->getMessage() == '') {
            movepage("process.php?$back_url");
        }
        movepage("process.php",$e->getMessage());
    }
?>


<html lang="ko">
<?php include('layout/header.php');?>
<section class="cont inner">
    <div class="contTitle">
        <h1>구매조회</h1>
        <h2>구매 후 생성된 QR코드를 확인할 수 있습니다</h2>
    </div>
    <div class="contSubmit">
        <div class="writeBox box">
            <table>
                <tr class="half">
                    <th scope="row">번호</th>
                    <td><?=$user_name?></td>
                    <th>입금액</th>
                    <td><?=number_format($real_price)?>원</td>
                </tr>
                <tr class="half">
                    <th scope="row">입금자명</th>
                    <td><?=$user_name?></td>
                    <th>신청일</th>
                    <td><?=$user_name?></td>
                </tr>
                <tr>
                    <th>QR코드</th>
                    <td colspan="3">
                        
                    <?php
                        if ($res_flag==2 && $qr_image_url!='') {
                            echo "<pre><img src='$qr_image_url'></pre>";
                        } else {
                            echo "<pre><font color='red'>스캔을 완료하였습니다.</font></pre>";
                        } 
                    ?>

                    </td>
                </tr>
            </table>
        </div>
        <div class="btnBox">
            <button type="button" class="color01" onclick="location.replace('process.php?<?=$back_url?>');">목록</button>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
<script type="text/javascript">
    let detail_token = '<?=$token?>';
    scanConfirm(detail_token);
</script>
</html>