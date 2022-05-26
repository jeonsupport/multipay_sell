<!DOCTYPE html>
<html lang="ko">
<?php include('layout/header.php');?>
<section class="cont inner">
    <div class="contTitle">
        <h1>문의사항</h1>
        <h2>언제든지 고객센터로 연락주시면 신속히 해결해드리겠습니다</h2>
    </div>
    <div class="contSubmit">
        <div class="writeBox box writeBoxQna">
            <form name="form" method="post" onsubmit="return wrSend()" action="./action/write_action.php">
                <input type="hidden" name="kind" value="user" />
                <table>
                    <tr>
                        <th>제목</th>
                        <td><input type="text" placeholder="제목을 입력해주세요" name="title" oninput="maxLengthCheck(this)" maxlength='50'></td>
                    </tr>
                    <tr>
                        <th>작성자</th>
                        <td><input type="text" placeholder="작성자를 입력해주세요" name="name" oninput="maxLengthCheck(this)" maxlength='10'></td>
                    </tr>
                    <tr>
                        <th>내용</th>
                        <td><textarea rows="3" placeholder="내용을 입력해주세요" name="contents" oninput="maxLengthCheck(this)" maxlength='1500'></textarea></td>
                    </tr>
                    <!-- <tr> -->
                        <!-- <th>비밀번호</th> -->
                        <!-- <td><input type="password" placeholder="4자리 입력" oninput="maxLengthCheck(this)" maxlength='4' name="pw"></td> -->
                    <!-- </tr> -->
                    <tr>
                        <th><img src="captcha.php?date=<?echo date('h:i:s')?>"></th>
                        <td><input type="text" placeholder="왼쪽 이미지의 텍스트를 입력해주세요" oninput="maxLengthCheck(this)" maxlength='5' name="captcha"></td>
                    </tr>
                </table>
            </div>
            <div class="btnBox">
                <button type="button" onclick="location.href='qna.php'">취소</button>
                <button type="submit" class="color02">저장</button>
            </div>
        </form>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>