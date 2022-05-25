<?php
    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : "";
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";

    $back_url = "?page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword";
?>
<!DOCTYPE html>
<html lang="ko">
<?php include('layout/header.php');?>
<section class="cont inner">
    <div class="contTitle">
        <h1>문의사항</h1>
        <h2>언제든지 고객센터로 연락주시면 신속히 해결해드리겠습니다</h2>
    </div>
    <div class="contSubmit qna">
        <div class="passBox box">
            <form method='post' action='./action/login_action.php'>
                <ul>
                    <li>비밀번호</li>
                    <input type="hidden" name="seq_no" value="<?=$seq_no?>" />
                    <input type="hidden" name="page" value="<?=$page?>" />
                    <input type="hidden" name="sch_field" value="<?=$sch_field?>" />
                    <input type="hidden" name="sch_keyword" value="<?=$sch_keyword?>" />
                    <li><img src="assets/img/i_lock.png"></li>
                    <li><input type="password" placeholder="4자리 입력" oninput="maxLengthCheck(this)" maxlength='4' name="pw"></li>
                    <li>
                        <button type="button" onclick="location.href='qna.php?<?=$back_url?>'">이전</button>
                        <button type="submit">확인</button>
                    </li>
                </ul>
            </form>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>