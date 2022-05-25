<?php
    session_start();

    if(!isset($_SESSION['AdminID']) || empty($_SESSION['AdminID'])) {
        movepage("login.php", "잘못된 접근입니다.");
    }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>바우처이지패스 관리자</title>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
<meta name="format-detection" content="telephone=no">
<link href="assets/img/favicon.ico" rel="icon">
<link href="assets/img/i_logo.png" rel="apple-touch-icon">
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/base.css" rel="stylesheet">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/jquery/jquery-migrate.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header id="header">
    <div class="h_bg"></div>
    <div class="inner">
        <h1 class="logo">
            <a href="process.php"><img src="assets/img/logo.png" alt="바우처 이지 패스 로고"></a>
        </h1>
        <div class="gnb" id="gnb">
            <ul>
                <li class="btnNav">
                    <i class="fa fa-bars ico_m" aria-hidden="true"></i>
                    <i class="fa fa-times ico_x" aria-hidden="true"></i>
                </li>
                <li class="nav"><a href="process.php">구매조회</a></li>
                <!-- <li class='nav'><a href="void(0);" onclick="alert('준비 중입니다.'); return false;">통계</a></li> -->
                <li class="nav"><a href="statistics.php">통계</a></li>
                <li class="nav"><a href="management.php">상품권관리</a></li>
                <li class="nav"><a href="deposit_list.php">입금리스트</a></li>
                <li class="nav"><a href="purchase_error_list.php">ERRORLOG 리스트</a></li>
                <li class="nav"><a href="upload.php">핀번호업로드</a></li>
                <!-- <li class="nav"><a href="upload_fail_list.php">핀번호업로드 실패리스트</a></li> -->
                <li class="nav"><a href="qna.php">문의사항</a></li>
                <li class="nav"><a href="members.php">계정관리</a></li>
            </ul>
        </div>
    </div>
</header>
<script>
    $('.ico_m').click(function(){
        $('.ico_m').hide();
        $('.ico_x').show();
        $('#header .gnb ul li').show();
    });
    $('.ico_x').click(function(){
        $('.ico_x').hide();
        $('.ico_m').show();
        $('#header .gnb ul li').hide();
    });
</script>