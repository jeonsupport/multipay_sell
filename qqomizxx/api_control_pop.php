<?php
    include_once "../db_connecter.php";

    $conn = new MsSQL_Connecter();
    $db = $conn->ConnectServer();

    try {
        $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : 0;
        $action = isset($_GET['action']) ? $_GET['action'] : 'modify';

        $query = "SELECT * FROM A_BuySiteAPIControl WHERE SeqNo = $seq_no";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);


        $chain_name = isset($row['ChainName']) ? $row['ChainName'] : '';
        $token = isset($row['Token']) ? $row['Token'] : '';
        $price = isset($row['Price']) ? $row['Price'] : '';
        $free_ymd = isset($row['FreeYMD']) ? date('Y-m-d', strtotime($row['FreeYMD'])) : '';
        $pay_ymd = isset($row['PayYMD']) ? date('Y-m-d', strtotime($row['PayYMD'])) : '';
        $end_free_month = isset($row['EndFreeMonth']) ? $row['EndFreeMonth'] : '';
        $end_pay_month = isset($row['EndPayMonth']) ? $row['EndPayMonth'] : '';
        

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
<form method = "post" action = "./action/api_control_action.php">
<table border="1" class='poptable'>
    <input type="hidden" name="seq_no" value="<?=$seq_no?>" />
    <input type="hidden" name="action" value="<?=$action?>" />
    <tr>
        <th>가맹점명</th>
        <td><input type="text" name="chain_name" value="<?=$chain_name?>"/></td>
    </tr>
    <tr>
        <th>개인키</th>
        <td><input type="text" name="token" value="<?=$token?>"/></td>
    </tr>
    <?php if ($action == 'modify') { ?>
    <tr>
        <th>무료사용신청일</th>
        <td><input type="date" name="free_date" value="<?=$free_ymd?>"/></td>
    </tr>
    <tr>
        <th>유료사용신청일</th>
        <td><input type="date" name="pay_date" value="<?=$pay_ymd?>"/></td>
    </tr>
    <tr>
        <th>무료사용마감일</th>
        <td>
            <select name="end_free">
                <option value='1' <?php if($end_free_month=='1') echo 'selected' ?>>1개월</option>
                <option value='2' <?php if($end_free_month=='2') echo 'selected' ?>>2개월</option>
                <option value='3' <?php if($end_free_month=='3') echo 'selected' ?>>3개월</option>
                <option value='4' <?php if($end_free_month=='4') echo 'selected' ?>>4개월</option>
                <option value='5' <?php if($end_free_month=='5') echo 'selected' ?>>5개월</option>
                <option value='6' <?php if($end_free_month=='6') echo 'selected' ?>>6개월</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>유료사용마감일</th>
        <td>
            <select name="end_pay">
                <option value='1' <?php if($end_pay_month=='1') echo 'selected' ?>>1개월</option>
                <option value='2' <?php if($end_pay_month=='2') echo 'selected' ?>>2개월</option>
                <option value='3' <?php if($end_pay_month=='3') echo 'selected' ?>>3개월</option>
                <option value='4' <?php if($end_pay_month=='4') echo 'selected' ?>>4개월</option>
                <option value='5' <?php if($end_pay_month=='5') echo 'selected' ?>>5개월</option>
                <option value='6' <?php if($end_pay_month=='6') echo 'selected' ?>>6개월</option>
            </select>
        </td>
    </tr>
    <?php } ?>
</table>
<div class="btnArea mid">
    <button type="submit" class="btn btn-primary">저장</button>
</div>
</form>
</div>
</body>
