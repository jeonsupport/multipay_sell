<?php

    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();

    // 페이징
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";
    $where_cond = '';
    if($sch_keyword == '') {
        $where_cond = '';
    } else {
        $where_cond = "WHERE user_name LIKE CONCAT( '%', :sch_keyword, '%')";
    }

    $pageSize = 20;
    $startRow = ($page-1) * $pageSize;
    $url = $_SERVER['PHP_SELF'];

    try {

        $query = "SELECT COUNT(*) AS cnt FROM shop_user_deposit_list {$where_cond}";
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
        die($e->getMessage());
    }
    
?>


<!DOCTYPE html>
<html lang="ko">
<?php include('header.php');?>
<meta name="viewport" content="width=1280">
<body>
    <section class="sub_bo_Wrap">
        <div class="subTopBox">
            
            <div class="subT_SideBox">
                <div class="admin_btn">
                    <p class="btn btn-light"><?=$_SESSION['AdminID']." 님"?></p>
                    <button type="button" class="btn btn-danger" onclick="location.replace('./action/logout.php');">로그아웃</button>
                </div>
                <div class="subtName">
                    <h1>입금 리스트</h1>
                    <h2>실입금 또는 관리자가 입금현황 처리상태를 변경했을 경우 해당 내역을 확인할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="deposit">
            <div class="process_sideBox">
                <form class="frm" name="schfrm" id="schfrm" action="./deposit_list.php">
                    <p class="cell text-center">
                        <input type="text" name="sch_keyword" value="<?=$sch_keyword?>" placeholder="이름 검색">
                        <button type="submit" class="btn go">조회</button>
                    </p>
                </form>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">번호</td>
                                <td class="re_top">은행명</td>
                                <td class="re_top">입금자명</td>
                                <td class="re_top">금액</td>
                                <td class="re_top">날짜</td>
                                <td class="re_top">에러로그</td>
                                <td class="re_top">입금유형</td>
                            </tr>

                            <?php
                            try {
       
                                $query = "
                                    SELECT seq_no, bank_name, user_name, real_price, reg_date, memo, admin_check FROM shop_user_deposit_list
                                    {$where_cond}
                                    ORDER BY reg_date DESC
                                    LIMIT {$startRow}, {$pageSize}
                                ";
                                        
                                $statement = $db->prepare($query);
                                $statement->bindValue(':sch_keyword', $sch_keyword);
                                $statement->execute();
                                $rowCount = $statement->rowCount();

                            }catch(PDOException $e) {
                                echo $e->getMessage();
                            }


                            $rcnt = 0;
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                
                                $rcnt++;

                                $no = $rcnt + $startRow;
                                $seq_no = isset($row['seq_no']) ? $row['seq_no'] : "-";
                                $bank_name = isset($row['bank_name']) ? $row['bank_name'] : "-";
                                $user_name = isset($row['user_name']) ? $row['user_name'] : "-";
                                $real_price = isset($row['real_price']) ? number_format($row['real_price']).'원' : "-";
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "-";
                                $memo = isset($row['memo']) ? $row['memo'] : "-";
                                $admin_check = isset($row['admin_check']) ? $row['admin_check'] : 0;


                                if($admin_check == 1) {
                                    $admin_check = "<p style='color:var(--bs-red)'>관리자변경</p>";
                                } else {
                                    $admin_check = "<p style='color:var(--bs-blue)'>입금완료</p>";
                                }
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid"><?=$seq_no?></td>
                                <td class="re_mid"><?=$bank_name?></td>
                                <td class="re_mid"><?=$user_name?></td>
                                <td class="re_mid"><?=$real_price?></td>
                                <td class="re_mid"><?=$reg_date?></td>
                                <td class="re_mid"><?=$memo?></td>
                                <td class="re_mid"><?=$admin_check?></td>
                            </tr>
                            <?php }?>
                        </table>
                        <div class="d-flex justify-content-center"><?=$pagination?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="assets/js/basket.js"></script>
</body>
</html>