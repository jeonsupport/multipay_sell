<?php
    include_once "../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    try {
        $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : '';
        $product_no = isset($_GET['product_no']) ? $_GET['product_no'] : '';

        $query = "
                SELECT * FROM
                (SELECT seq_no, product_no, product_name, discount_comm_rate, publisher_comm_rate, use_flag FROM shop_product_list WHERE seq_no = $seq_no) AS product_list
                JOIN
                (SELECT use_flag AS p1000 from shop_product_use_list where product_no = $product_no and price = 1000) AS face_amt1000
                JOIN
                (SELECT use_flag AS p3000 from shop_product_use_list where product_no = $product_no and price = 3000) AS face_amt3000
                JOIN
                (SELECT use_flag AS p5000 from shop_product_use_list where product_no = $product_no and price = 5000) AS face_amt5000
                JOIN
                (SELECT use_flag AS p10000 from shop_product_use_list where product_no = $product_no and price = 10000) AS face_amt10000
                JOIN
                (SELECT use_flag AS p30000 from shop_product_use_list where product_no = $product_no and price = 30000) AS face_amt30000
                JOIN
                (SELECT use_flag AS p50000 from shop_product_use_list where product_no = $product_no and price = 50000) AS face_amt50000
        ";

        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        //product_list
        $seq_no = isset($row['seq_no']) ? $row['seq_no'] : '';
        $product_no = isset($row['product_no']) ? $row['product_no'] : '';
        $product_name = isset($row['product_name']) ? $row['product_name'] : '';
        $discount_comm_rate = isset($row['discount_comm_rate']) ? $row['discount_comm_rate'] : '';
        $publisher_comm_rate = isset($row['publisher_comm_rate']) ? $row['publisher_comm_rate'] : '';
        $use_flag = isset($row['use_flag']) ? $row['use_flag'] : '';

        //사용 권종 추출
        $p1000 = isset($row['p1000']) ? $row['p1000'] : '';
        $p3000 = isset($row['p3000']) ? $row['p3000'] : '';
        $p5000 = isset($row['p5000']) ? $row['p5000'] : '';
        $p10000 = isset($row['p10000']) ? $row['p10000'] : '';
        $p30000 = isset($row['p30000']) ? $row['p30000'] : '';
        $p50000 = isset($row['p50000']) ? $row['p50000'] : '';

        $arr_face_amt = array();
        $arr_face_amt = array(
            "1000"  => $p1000,
            "3000"  => $p3000,
            "5000"  => $p5000,
            "10000" => $p10000,
            "30000" => $p30000,
            "50000" => $p50000
        );

        if($use_flag==1) {
            $use_flag = '화면표시';
        } else {
            $use_flag = '화면숨김';
        }

        if($rowCount==0) {
            throw new Exception('error!!!');
        }

    } catch(PDOException $e) {
        die("database error");
    } catch(Exception $e) {
        die($e->getMessage());
    }
?>
<head>
<meta charset="utf-8">
<title>관리자 - 바우처 이지 패스</title>
<meta content="width=device-width, initial-scale=1.0, user-scalable=0" name="viewport">
<meta name="format-detection" content="telephone=no">
<link href="assets/img/favicon.ico" rel="icon">
<link href="assets/img/i_logo.png" rel="apple-touch-icon">
<link href="assets/vendor/animate/animate.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
<link href="assets/css/base.css" rel="stylesheet">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/jquery/jquery-migrate.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class='t_view'>
<form method = "post" action = "./action/management_pop_action.php">
<table border="1" class='poptable'>
    <input type="hidden" name="action" value="change_management" />
    <input type="hidden" name="seq_no" value="<?=$seq_no?>" />
    <input type="hidden" name="product_no" value="<?=$product_no?>" />
    <tr>
        <th>상품권코드</th>
        <td><?=$product_no?></td>
    </tr>
    <tr>
        <th>상품권명</th>
        <td><input type="text" name="product_name" value="<?=$product_name?>"/></td>
    </tr>
    <tr>
        <th>할인율</th>
        <td><input type="text" name="discount_comm_rate" value="<?=$discount_comm_rate?>"/>%</td>
    </tr>
    <tr>
        <th>발행사수수료</th>
        <td><input type="text" name="publisher_comm_rate" value="<?=$publisher_comm_rate?>"/>%</td>
    </tr>
    <tr>
        <th>상태</th>
        <td><?=$use_flag?></td>
    </tr>
    <tr>
        <th>사용권종</th>
        <td>
            <?php
            
                foreach($arr_face_amt as $k => $v) {
               
                    echo $k."<p>원권</p>";
                  
            ?>
            <input 
                type="radio" 
                <?php if($v==1) echo "checked"; ?>
                value=1
                id=<?='use'.$k?>
                name=<?='p'.$k?>>
            <label for=<?='use'.$k?>>사용</label>
            <input 
                type="radio" 
                <?php if($v==2) echo "checked"; ?>
                value=2
                id=<?='nuse'.$k?>
                name=<?='p'.$k?>>
            <label for=<?='nuse'.$k?>>미사용</label>
            <br>

            <?php } ?>

                
        </td>
    </tr>
</table>
<div class="btnArea mid">
    <button type="submit" class="btn btn-primary">저장</button>
</div>
</form>
</div>
</body>
