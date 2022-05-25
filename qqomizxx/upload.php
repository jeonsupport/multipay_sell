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

        $query = "SELECT COUNT(*) AS cnt, (SELECT balance FROM shop_balance WHERE use_state = 1) as balance FROM shop_qr_serial_code_list";
        $statement = $db->query($query);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $totRecord = isset($row['cnt']) ? $row['cnt'] : '';
        $balance   = isset($row['balance']) ? $row['balance'] : '';
    
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
                    <h1>핀번호 업로드</h1>
                    <h2>QR토큰번호를 업로드할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="pinupload">
            <div class="process_sideBox">
                <!-- 엑셀 업로드 -->
                <table class="table table-bordered upload">
                    <form action="./action/read.php" method="post" onsubmit="return form_check(this);">
                        <tr>
                            <th>개별 업로드</th>
                            <td>
                                <input type="text" class="form-control" name="token" placeholder="핀번호를 입력해주세요"/>
                                <button type="submit" class="btn btn-secondary btn-lg" >업로드</button>
                            </td>
                        </tr>
                    </form>
                </table>
                <table class="table table-bordered upload">
                    <tr>
                        <th>현재 잔액</th>
                        <td><?=number_format($balance)?></td>
                    </tr>
                </table>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">번호</td>
                                <td class="re_top">핀코드</td>
                                <td class="re_top">상품권 번호</td>
                                <td class="re_top">상품권명</td>
                                <td class="re_top">이전금액</td>
                                <td class="re_top">합계금액</td>
                                <td class="re_top">합계 개수</td>
                                <td class="re_top">등록ID</td>
                                <td class="re_top">등록IP</td>
                                <td class="re_top">등록일</td>
                            </tr>
                            <?php
                            try {
                                $query = 
                                    "
                                        SELECT seq_no, serial_code, product_no, product_name, before_price, total_price, total_count, admin_ip, admin_id, reg_date FROM shop_qr_serial_code_list
                                        ORDER BY reg_date DESC, seq_no DESC
                                        LIMIT {$startRow}, {$pageSize}
                                    ";
                                        
                                $statement = $db->query($query);
                                $rowCount = $statement->rowCount();

                            }catch(PDOException $e) {
                                echo $e->getMessage();
                            }


                            $rcnt = 0;
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                
                                $rcnt++;

                                $no = $rcnt + $startRow;
                                $seq_no       = isset($row['seq_no'])       ? $row['seq_no']       : '';
                                $serial_code  = isset($row['serial_code'])  ? $row['serial_code']  : '';
                                $price        = isset($row['price'])        ? $row['price']        : '';
                                $product_no   = isset($row['product_no'])   ? $row['product_no']   : '';
                                $product_name = isset($row['product_name']) ? $row['product_name'] : '';
                                $before_price = isset($row['before_price']) ? $row['before_price'] : '';
                                $total_price  = isset($row['total_price'])  ? $row['total_price']  : '';
                                $total_count  = isset($row['total_count'])  ? $row['total_count']  : '';
                                $admin_ip     = isset($row['admin_ip'])     ? $row['admin_ip']     : '';
                                $admin_id     = isset($row['admin_id'])     ? $row['admin_id']     : '';
                                $reg_date     = isset($row['reg_date'])     ? $row['reg_date']     : '';

                                // $serial_code = substr($serial_code,0, 15)." ... ";
                                
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid"><?=$seq_no?></td>
                                <td class="re_mid"><?=$serial_code?></td>
                                <td class="re_mid"><?=$product_no?></td>
                                <td class="re_mid"><?=$product_name?></td>
                                <td class="re_mid"><?=number_format($before_price).'원'?></td>
                                <td class="re_mid"><?=number_format($total_price).'원'?></td>
                                <td class="re_mid"><?=$total_count?></td>
                                <td class="re_mid"><?=$admin_ip?></td>
                                <td class="re_mid"><?=$admin_id?></td>
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
    <script>

        function form_check(f) {

            if(f.token.value=='') {
                alert("핀번호를 입력해주세요.");
                return false;
            }
            
            f.submit();
        }
        
        function excel_form_check(f) {

            if(f.excelFile.value=='') {
                alert("엑셀파일을 업로드해주세요.");
                return false;
            }
            
            f.submit();
        }
    </script>
</body>
</html>