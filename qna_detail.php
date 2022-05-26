<?php
    session_start();

    include_once "db_connecter.php";
    include_once "paging.php";
    include_once "inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : "";
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";

    $back_url = "?page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword";

    if(!isset($_SESSION['seq_no']) || empty($_SESSION['seq_no']) || $_SESSION['seq_no'] != $seq_no) {
        movepage("qna.php","잘못된 접근입니다.");
    }

    try {
        $query = "SELECT title, writer, contents, reg_date FROM shop_notice_board WHERE seq_no = $seq_no AND use_flag = 0";
        $statement = $db->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        $title  = isset($row['title']) ? $row['title'] : "";
        $writer = isset($row['writer']) ? $row['writer'] : "";
        $contents = isset($row['contents']) ? $row['contents'] : "";
        $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "";

        if($contents=="" || $title=="" || $reg_date=="" || $rowCount==0) {
            throw new Exception("글이 없습니다.");
        }

        //조회수 올리기
        $basename = isset($_SERVER["HTTP_REFERER"]) ? basename($_SERVER["HTTP_REFERER"]) : "";
        if($basename == "login_action.php") {
            $query = "UPDATE shop_notice_board SET write_hit = write_hit + 1 WHERE seq_no = $seq_no AND use_flag = 0";
            upHit($query);
        }

    } catch(PDOException $e) {
        movepage("qna.php","database query error(1)");
    } catch(Exception $e) {
        movepage("qna.php",$e->getMessage());
    }

    function upHit($query) {
        try {

            $db = $GLOBALS['db'];
            $statement = $db->prepare($query);
            $statement->execute();
            $rowCount = $statement->rowCount();


            if($rowCount==0) {
                throw new Exception("조회수 업데이트 실패");
            }

            return;

        } catch(PDOException $e) {
            throw new Exception("database query error(2)");
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

?>

<!DOCTYPE html>
<html lang="ko">
<?php include('layout/header.php');?>
<section class="cont inner">
    <div class="contTitle">
        <h1>문의사항</h1>
        <h2>언제든지 고객센터로 연락주시면 신속히 해결해드리겠습니다</h2>
    </div>
    <div class="contSubmit">
        <div class="writeBox box">
            <table>
                <tr class="half">
                    <th scope="row">번호</th>
                    <td><?=$title?></td>
                    <th>작성자</th>
                    <td><?=$writer?></td>
                </tr>
                <tr class="half">
                    <th scope="row">제목</th>
                    <td><?=$title?></td>
                    <th>작성일</th>
                    <td><?=$title?></td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td colspan="3">
                        <pre><?=$contents?></pre>
                    </td>
                </tr>
            </table>
            <!-- <p class="reply">답글</p> -->
            <table>
                <?php

                    $query = "SELECT title, writer, contents, reg_date FROM shop_notice_board WHERE grp_no = $seq_no AND use_flag = 0";
                    $statement = $db->prepare($query);
                    $statement->execute();
                    $row=$statement->fetch(PDO::FETCH_ASSOC);
                    $rowCount = $statement->rowCount();
                        
                    $title = isset($row['title']) ? $row['title'] : "";
                    $writer = isset($row['writer']) ? $row['writer'] : "";
                    $contents = isset($row['contents']) ? $row['contents'] : "";
                    $reg_date = isset($row['reg_date']) ? substr($row['reg_date'], 0, 10) : "";
                        
                    $strHtml = "";
                    if($rowCount!=0) { // 답글 있는 경우
                        $strHtml .= "<p class=\"reply\">답글</p>";
                        $strHtml .= "<tr class=\"half\">";
                        $strHtml .= "<th scope=\"row\">등록일</th>";
                        $strHtml .= "<td>$reg_date</td>";
                        $strHtml .= "<th>작성자</th>";
                        $strHtml .= "<td>$writer</td>";
                        $strHtml .= "</tr>";
                        $strHtml .= "<tr>";
                        $strHtml .= "<th>답글 내용</th>";
                        $strHtml .= "<td colspan=\"3\">";
                        $strHtml .= "<pre>$contents</pre>";
                        $strHtml .= "</td>";
                        $strHtml .= "</tr>";

                        echo $strHtml;
                    }
                ?>
            </table>
        </div>
        <div class="btnBox">
            <button type="button" class="color01" onclick="location.href='qna.php<?=$back_url?>'">목록</button>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>