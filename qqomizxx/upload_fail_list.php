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

        $query = "SELECT COUNT(*) AS cnt FROM shop_fail_upload_excel_list";
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
                    <h1>핀번호 업로드 실패 리스트</h1>
                    <h2>QR토큰번호 업로드에 실패한 내역을 확인할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit">
            <div class="process_sideBox"> 
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top retWid15">번호</td>
                                <td class="re_top retWid16">실패 핀코드</td>
                                <td class="re_top retWid17">등록일</td>
                            </tr>

                            <?php
                            try {
       
                                $query = "
                                    SELECT seq_no, serial_code, reg_date
                                    FROM shop_fail_upload_excel_list
                                    ORDER BY reg_date DESC, seq_no DESC
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
                                $serial_code = isset($row['serial_code']) ? $row['serial_code'] : "";
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "";
                             
                                
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid"><?=$no?></td>
                                <td class="re_mid"><?=$serial_code?></td>
                                <td class="re_mid"><?=$reg_date?></td>
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