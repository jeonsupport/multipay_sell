<?php

    include_once "../inc.php";
    // $_SESSION['AdminID'] = 'admin';

    if(!isset($_SESSION['AdminID']) || empty($_SESSION['AdminID'])) {
        movepage("login.php", "잘못된 접근입니다.");
    }
?>

<div class="pop pop2">
    <form action="./action/join_action.php" id="login-form" method='post'>
        <h1>관리자 생성</h1>
        <div class="input-box">
            <input name='id' type="text" placeholder="아이디">
        </div>
        <div class="input-box">
            <input name='name' type="text" placeholder="이름">
        </div>

        <div class="input-box">
            <input name='pwd' type="password" placeholder="비밀번호">
        </div>

        <div class="input-box">
            <input name='pwdCheck' type="password" placeholder="비밀번호 확인">
        </div>
        <button class="login-btn" type="submit">계정 생성</button>
        <button class="popBtn closed" type="button">닫기</button>
    </form>
</div>