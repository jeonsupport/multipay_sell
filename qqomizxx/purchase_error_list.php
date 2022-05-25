<?php

    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();

    // 페이징
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
   
    $pageSize = 20;
    $startRow = ($page-1) * $pageSize;
    $url = $_SERVER['PHP_SELF'];

    try {

        $query = "SELECT COUNT(*) AS cnt FROM shop_purchase_fail_log";
        $statement = $db->query($query);
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
<body oncontextmenu='return false'>
    <section class="sub_bo_Wrap">
        <div class="subTopBox">
            
            <div class="subT_SideBox">
                <div class="admin_btn">
                    <p class="btn btn-light"><?=$_SESSION['AdminID']." 님"?></p>
                    <button type="button" class="btn btn-danger" onclick="location.replace('./action/logout.php');">로그아웃</button>
                </div>
                <div class="subtName">
                    <h1>ERROR LOG 리스트</h1>
                    <h2>실구매 실패한 경우 해당 내역을 확인할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="purchase">
            <div class="process_sideBox">
                <div class="oi_inputArea">
                    <div class="recentTableBox purch">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top w01">번호</td>
                                <td class="re_top w02">파라미터로그</td>
                                <td class="re_top w03">에러메시지</td>
                                <td class="re_top w04">날짜</td>
                            </tr>

                            <?php
                            try {
       
                                $query = "
                                    SELECT seq_no, str_param, error_msg, reg_date FROM shop_purchase_fail_log
                                    ORDER BY reg_date DESC
                                    LIMIT {$startRow}, {$pageSize}
                                ";
                                        
                                $statement = $db->prepare($query);
                                $statement->execute();
                                $rowCount = $statement->rowCount();

                            }catch(PDOException $e) {
                                echo $e->getMessage();
                            }


                            $rcnt = 0;
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                
                                $rcnt++;

                                $no = $rcnt + $startRow;
                                $seq_no = isset($row['seq_no']) ? $row['seq_no'] : "";
                                $str_param = isset($row['str_param']) ? $row['str_param'] : "-";
                                $error_msg = isset($row['error_msg']) ? $row['error_msg'] : "-";
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "-";
                                
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid w01"><?=$seq_no?></td>
                                <td class="re_mid w02"><?=$str_param?></td>
                                <td class="re_mid w03"><?=$error_msg?></td>
                                <td class="re_mid w04"><?=$reg_date?></td>
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