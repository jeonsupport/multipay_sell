<!DOCTYPE html>
<html lang="ko">
<?php include('layout/header.php');?>
<section class="sub_bo_Wrap">
    <div class="subT_SideBox">
        <div class="subtName">
            <h1>문의사항</h1>
            <h2>언제든지 고객센터로 연락주시면 신속히 해결해드리겠습니다</h2>
        </div>
    </div>
    <div id="process_submit">
        <div class="process_sideBox">
            <!-- <div class="top_findBox">QnA 글쓰기</div> -->
            <div class="oi_inputArea">
                <div class="recentTableBox">
                    <section class="con">
                        <div class="board">
                            <div class="t_view">
                                <form name="form" method="post" onsubmit="return wrSend()" action="./action/write_action.php">
                                    <input type="hidden" name="kind" value="user" />
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th scope="row">제목</th>
                                                <td><input type="text" class="form-control cs-a" placeholder="제목" name="title" oninput="maxLengthCheck(this)" maxlength='50'></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">작성자</th>
                                                <td><input type="text" class="form-control cs-a" placeholder="작성자" name="name" oninput="maxLengthCheck(this)" maxlength='10'></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">내용</th>
                                                <td><textarea class="form-control" rows="3" name="contents" oninput="maxLengthCheck(this)" maxlength='1500'></textarea></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">비밀번호</th>
                                                <td><input type="password" class="form-control cs-a" placeholder="4자리 입력" oninput="maxLengthCheck(this)" maxlength='4' name="pw"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><img src="captcha.php?date=<?echo date('h:i:s')?>"></th>
                                                <td><input type="text" class="form-control cs-a" placeholder="왼쪽 이미지의 글자를 입력하세요." oninput="maxLengthCheck(this)" maxlength='5' name="captcha"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="btnArea">
                                        <button type="submit" class="btn btn-primary fr">저장</button>
                                        <button type="button" class="btn btn-outline-primary fr" onclick="location.href='qna.php'">취소</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>