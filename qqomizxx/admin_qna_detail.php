<?php

    // DB connect
    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $page = isset($_GET['page']) ? $_GET['page'] : "";
    $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : "";
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";

    $back_url = "?page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword";

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
        $basename = substr($basename, 0, 3);
        if($basename == "qna") {
            $query = "UPDATE shop_notice_board SET write_hit = write_hit + 1 WHERE seq_no = $seq_no AND use_flag = 0";
            upHit($query);
        }
        

    } catch(PDOException $e) {
        msgback("database query error(1)");
    } catch(Exception $e) {
        msgback($e->getMessage());
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
<?php include('header.php');?>
<body oncontextmenu='return false'>
    <section class="sub_bo_Wrap">
        <div class="subTopBox">
            
            <div class="subT_SideBox">
                <div class="admin_btn">
                    <p class="btn btn-light"><?=$_SESSION['AdminID']." 님"?></p>
                    <button type="button" class="btn btn-danger" onclick="location.replace('./action/logout.php');">로그아웃</button>
                </div>
                <div class="subtName">
                    <h1>문의사항</h1>
                    <h2>고객님 문의사항에 답글을 남길 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit">
            <div class="process_sideBox">
                <div class="top_findBox">QnA 내용</div>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <section class="con">
                            <div class="board">
                                <div class="t_view">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th scope="row">제목</th>
                                                <td><?=$title?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">작성자</th>
                                                <td><?=$writer?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">내용</th>
                                                <td class="article-text">
                                                    <pre><?=$contents?></pre>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="t_view">
                                    <p class="reply">답글</p>
                                    <form name="form" method="post" onsubmit="return admin_wrSend()" action="./action/write_action.php">
                                        <table>
                                            <?php

                                                $query = "SELECT seq_no, title, writer, contents, reg_date FROM shop_notice_board WHERE grp_no = $seq_no AND use_flag = 0";
                                                $statement = $db->prepare($query);
                                                $statement->execute();
                                                $row=$statement->fetch(PDO::FETCH_ASSOC);
                                                $rowCount = $statement->rowCount();
                                                    
                                                $r_seq_no = isset($row['seq_no']) ? $row['seq_no'] : "";
                                                $title = isset($row['title']) ? $row['title'] : "";
                                                $writer = isset($row['writer']) ? $row['writer'] : "";
                                                $contents = isset($row['contents']) ? $row['contents'] : "";
                                                $reg_date = isset($row['reg_date']) ? substr($row['reg_date'], 0, 10) : "";
                                                $reply_flag = 0;
                                                    
                                                $strHtml = "";
                                                $strHtml .= "<input type=\"hidden\" name=\"page\" value=$page />";
                                                $strHtml .= "<input type=\"hidden\" name=\"seq_no\" value=$seq_no />";
                                                $strHtml .= "<input type=\"hidden\" name=\"sch_field\" value='$sch_field' />";
                                                $strHtml .= "<input type=\"hidden\" name=\"sch_keyword\" value='$sch_keyword' />";
                                                if($rowCount!=0) { // 답글 있는 경우
                                                    $strHtml .= "<input type=\"hidden\" name=\"kind\" value=\"up_admin\" />";
                                                    $strHtml .= "<input type=\"hidden\" name=\"r_seq_no\" value=$r_seq_no />";
                                                    $strHtml .= "<tbody>";
                                                    $strHtml .= "<tr>";
                                                    $strHtml .= "<th scope=\"row\">등록일</th>";
                                                    $strHtml .= "<td>$reg_date</td>";
                                                    $strHtml .= "</tr>";
                                                    $strHtml .= "<tr>";
                                                    $strHtml .= "<th scope=\"row\">작성자</th>";
                                                    $strHtml .= "<td>$writer</td>";
                                                    $strHtml .= "</tr>";
                                                    $strHtml .= "<tr>";
                                                    $strHtml .= "<th scope=\"row\">내용</th>";
                                                    $strHtml .= "<td class=\"article-text\">";
                                                    $strHtml .= "<pre>$contents</pre>";
                                                    $strHtml .= "</td>";
                                                    $strHtml .= "</tr>";
                                                    $strHtml .= "<tr style='outline: 1px solid #999;'>";
                                                    $strHtml .= "<th scope=\"row\">답글<br class='m01'>수정</th>";
                                                    $strHtml .= "<td class=\"article-text\">";
                                                    $strHtml .= "<textarea rows=\"3\" placeholder=\"답글 수정하기...\" name=\"contents\"></textarea>";
                                                    $strHtml .= "<button type=\"submit\">확인</button>";
                                                    $strHtml .= "</td>";
                                                    $strHtml .= "</tr>";
                                                    $strHtml .= "</tbody>";

                                                } else { // 답글 없는 경우
                                                    $reply_flag = 1;
                                                    $strHtml .= "<input type=\"hidden\" name=\"kind\" value=\"in_admin\" />";
                                                    $strHtml .= "<tbody>";
                                                    $strHtml .= "<tr>";
                                                    $strHtml .= "<th scope=\"row\">답글</th>";
                                                    $strHtml .= "<td class=\"article-text\">";
                                                    $strHtml .= "<textarea rows=\"3\" placeholder=\"답글 작성하기...\" name=\"contents\"></textarea>";
                                                    $strHtml .= "<button type=\"submit\">확인</button>";
                                                    $strHtml .= "</td>";
                                                    $strHtml .= "</tr>";
                                                    $strHtml .= "</tbody>";
                                                }

                                                echo $strHtml;


                                                $reply_del_url = "./action/write_action.php?seq_no=$seq_no&r_seq_no=$r_seq_no&kind=r_del&page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword";
                                                $post_del_url  = "./action/write_action.php?seq_no=$seq_no&kind=p_del&page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword";
                                            ?>
                                                
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </section>
                        <div class="btnArea">
                            <button type="button" class="btn sizeL btn-outline-primary fl" onclick="reply_del('<?=$reply_del_url?>', <?=$reply_flag?>)">답글 삭제</button>
                            <button type="button" class="btn sizeL btn-outline-primary fl" onclick="post_del('<?=$post_del_url?>')">게시물 삭제</button>
                            <button type="button" class="btn btn-primary fr btnM" onclick="location.href='./qna.php<?=$back_url?>'">목록</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="assets/js/basket.js"></script>
</body>
</html>