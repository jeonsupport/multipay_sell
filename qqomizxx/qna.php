<?php
    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";
    $sch_cond = "";

    $where_buff = array();
    $where_buff[] = "use_flag = 0";
    $where_buff[] = "depth = 0";
    if($sch_field!="" && $sch_keyword!="") {
        if($sch_field) {
            switch($sch_field) {
                case 'name':
                    $where_buff[] = "writer LIKE CONCAT( '%', :sch_keyword, '%')";
                    break;
                case 'title':
                    $where_buff[] = "title LIKE CONCAT( '%', :sch_keyword, '%')";
                    break;
            }
        }
    }

    $where_cond = $where_buff ? " WHERE ".implode(" AND ", $where_buff) : "";

    // 페이징
    $pageSize = 20;
    $startRow = ($page-1) * $pageSize;
    $url = $_SERVER['PHP_SELF'];
    
    try {
    
        $query = "SELECT COUNT(*) AS cnt FROM shop_notice_board {$where_cond}";
        $statement = $db->prepare($query);
        $statement->bindValue(':sch_keyword', $sch_keyword);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $totRecord = isset($row['cnt']) ? $row['cnt'] : "";
    
        $config = array(
          'base_url' => $url,
          'page_rows' => $pageSize,
          'total_rows' => $totRecord
        );
    
        $paging->initialize($config);
        $pagination = $paging->create();
        
    } catch(PDOException $e) {
        die("database error");
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
        <div id="process_submit" class="qnalist">
            <div class="process_sideBox">
                <form class="frm" name="schfrm" id="schfrm" action="qna.php">
                    <p class="cell text-center">
                        <select name="sch_field" id="sch_field">
                            <option value="name" <?php if($sch_field=='name') echo 'selected'?>>이름</option>
                            <option value="title" <?php if($sch_field=='title') echo 'selected'?>>제목</option>
                        </select>
                        <input type="text" name="sch_keyword" value="<?=$sch_keyword?>" placeholder="검색할 단어 입력">
                        <button type="submit" class="btn go fid">검색</button>
                    </p>
                </form>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">번호</td>
                                <td class="re_top">제목</td>
                                <td class="re_top">작성자</td>
                                <td class="re_top">날짜</td>
                                <td class="re_top">조회수</td>
                            </tr>

                            <?php
                                try {


                                    $query = "
                                        SELECT seq_no, grp_no, grp_ord, title, writer, contents, reg_date, use_flag, pwd, write_hit FROM shop_notice_board 
                                        {$where_cond} 
                                        ORDER BY reg_date DESC
                                        LIMIT {$startRow}, {$pageSize}
                                    ";

                                    $statement = $db->prepare($query);
                                    $statement->bindValue(':sch_keyword', $sch_keyword);
                                    $statement->execute();
                                    $rowCount = $statement->rowCount();

                                } catch(PDOException $e) {
                                    echo $e->getMessage();
                                }


                                $rcnt = 0;
                                $strHTML = "";
                                while($row=$statement->fetch(PDO::FETCH_ASSOC)) {

                                    $rcnt++;

                                    $no = $rcnt + $startRow;
                                    $seq_no = isset($row['seq_no']) ? $row['seq_no'] : "";
                                    $grp_no = isset($row['grp_no']) ? $row['grp_no'] : "";
                                    $grp_ord = isset($row['grp_ord']) ? $row['grp_ord'] : "";
                                    $title = isset($row['title']) ? $row['title'] : "";
                                    $writer = isset($row['writer']) ? $row['writer'] : "";
                                    $contents = isset($row['contents']) ? $row['contents'] : "";
                                    $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "";
                                    $write_hit = isset($row['write_hit']) ? $row['write_hit'] : "";

                                    
                                    // html 
                                    $strHTML .= "<tr class=\"rem_tr\">";
                                    
                                    $strHTML .= "<td class=\"re_mid\">$no</td>";
                                    $strHTML .= "<td class=\"re_mid\">";
                                    $strHTML .= "<a href=\"./admin_qna_detail.php?page=$page&seq_no=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword\">";
                                    $strHTML .= $title;
                                    // $strHTML .= "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i>";
                                    // 운영자가 답글 달았으면
                                    if($grp_ord!=1) {
                                        $strHTML .= "<span class=\"ic_red\">답변완료</span>";
                                        
                                    }
                                    $strHTML .= "</a>";
                                    $strHTML .= "</td>";
                                    $strHTML .= "<td class=\"re_mid\">$writer</td>";
                                    $strHTML .= "<td class=\"re_mid\">$reg_date</td>";
                                    $strHTML .= "<td class=\"re_mid\">$write_hit</td>";
                                    $strHTML .= "</tr>";
                                }

                                echo $strHTML;
                            ?>

                        </table>
                        <div class="d-flex justify-content-center"><?=$pagination?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>