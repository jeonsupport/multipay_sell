<?php
    include_once "db_connecter.php";
    include_once "paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();


    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? strip_tags($_GET['sch_keyword']) : "";
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
<?php include('layout/header.php');?>
<section class="cont inner">
    <div class="contTitle">
        <h1>문의사항</h1>
        <h2>언제든지 고객센터로 연락주시면 신속히 해결해드리겠습니다</h2>
    </div>
    <div class="contSubmit qna">
        <div class="searchBox">
            <form name="schfrm" id="schfrm" action="qna.php">
                <select name="sch_field" id="sch_field">
                    <option value="name" <?php if($sch_field=='name') echo 'selected'?>>이름</option>
                    <option value="title" <?php if($sch_field=='title') echo 'selected'?>>제목</option>
                </select>
                <input type="text" name="sch_keyword" value="<?=$sch_keyword?>" placeholder="검색할 단어 입력">
                <button type="submit">검색</button>
            </form>
        </div>
        <div class="tableBox box">
            <table>
                <tr class="thead">
                    <td class="">번호</td>
                    <td class="">제목</td>
                    <td class="">작성자</td>
                    <td class="">날짜</td>
                    <td class="">조회수</td>
                </tr>

                <?php
                    try {
                        // $query = "
                        //             SELECT seq_no, grp_no, grp_ord, title, writer, contents, reg_date, use_flag, pwd, write_hit FROM (
                        //             SELECT ROW_NUMBER() OVER (ORDER BY seq_no DESC) AS rn, *
                        //             FROM shop_user_purchase_list {$where_cond} 
                        //             ) AS x
                        //             WHERE rn BETWEEN {$startRow}+1 AND {$startRow}+{$pageSize}
                        //             ORDER BY reg_date DESC
                        //         ";

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
                        $writer = isset($row['writer']) ? preg_replace('/.(?=.$)/u','*', $row['writer']) : "";
                        $contents = isset($row['contents']) ? $row['contents'] : "";
                        $reg_date = isset($row['reg_date']) ? substr($row['reg_date'], 0, 10) : "";
                        $write_hit = isset($row['write_hit']) ? $row['write_hit'] : "";
                        
                        // html 
                        $strHTML .= "<tr onClick=\"location.href='qna_pass.php?page=$page&seq_no=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword'\" class=\"tbody\">";
                        $strHTML .= "<td class=\"2\">$no</td>";
                        $strHTML .= "<td class=\"8\">";
                        $strHTML .= $title;
                        $strHTML .= "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i>";
                        if($grp_ord!=1) {
                            $strHTML .='<i class="fa fa-check" aria-hidden="true"></i>';
                        }
                        $strHTML .= "</td>";
                        $strHTML .= "<td class=\"retWid09\">$writer</td>";
                        $strHTML .= "<td class=\"retWid10\">$reg_date</td>";
                        $strHTML .= "<td class=\"retWid11\">$write_hit</td>";
                        $strHTML .= "</tr>";
                    }

                    echo $strHTML;
                ?>

            </table>
            <?=$pagination?>
        </div>
        <div class="btnBox">
            <button type="button" onclick="location.href='qna_write.php'">글쓰기</button>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>