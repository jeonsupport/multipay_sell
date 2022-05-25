<?php

    $id = isset($_GET['id']) ? $_GET['id'] : '';
?>


<div class="pop pop3">
    <form action="./action/modify_action.php" id="login-form" method='post'>
        <h1>비밀번호 수정</h1>
        <div class="input-box">
            <input name='id' type="text" value="<?=$id?>" readonly> 
        </div>
        <div class="input-box">
            <input name='bpwd' type="password" placeholder="현재 비밀번호">
        </div>
        <div class="input-box">
            <input name='pwd' type="password" placeholder="신규 비밀번호">
        </div>
        <div class="input-box">
            <input name='pwdCheck' type="password" placeholder="신규 비밀번호 확인">
        </div>
        <button class="login-btn" type="submit">수정하기</button>
        <button class="popBtn closed" type="button">닫기</button>
    </form>
</div>

<script>
    <?php
    if($id != ''){
    ?>
        $(".pop.pop3").fadeIn(300);
        $(".popBg").fadeIn(300);
    <?php
    }
    ?>
    $(".popBtn.closed").click(function () {
        $(".pop.pop1, .pop.pop2, .pop.pop3").fadeOut(300);
        $(".popBg").fadeOut(300);
    });
</script>