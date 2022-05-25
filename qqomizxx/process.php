<?php

    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();


    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $startDate = isset($_GET['dateA']) ? $_GET['dateA'] : date("Y-m-d");
    $endDate   = isset($_GET['dateB']) ? $_GET['dateB'] : date("Y-m-d");
    $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
    $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";
    $sch_cond = $sch_field.$sch_keyword;

    if(!isset($_GET['dateA'])){
        $s_time = strtotime("-1 months");

        $startDate = date("Y-m-d", $s_time);
        $endDate   = date("Y-m-d");
    }


    //-----------------------
    // 페이징 관련
    //-----------------------
    $pageSize = 20;
    $startRow = ($page-1) * $pageSize;
    $url = $_SERVER['PHP_SELF'];

    try {

        $query = "SELECT COUNT(*) AS cnt FROM shop_user_purchase_list WHERE phone_no LIKE CONCAT( '%', :sch_keyword, '%') AND reg_date BETWEEN :startDate AND DATE_ADD(:endDate, INTERVAL 1 DAY)";
        $statement = $db->prepare($query);
        $statement->bindValue(':sch_keyword', $sch_cond);
        $statement->bindValue(':startDate', $startDate);
        $statement->bindValue(':endDate', $endDate);
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
        die("database query error");
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
                    <h1>실시간 구매 조회</h1>
                    <h2>고객님 휴대폰 번호로 검색하시면 구매내역을 확인할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="process">
            <div class="process_sideBox">
                <form class="frm" name="schfrm" id="schfrm" action="./process.php">
                    <p class="cell text-center">
                        <input type="date" class="dateA" name="dateA" value="<?=$startDate?>"> ~ 
                        <input type="date" class="dateB" name="dateB" value="<?=$endDate?>">
                        <select name="sch_field" id="sch_field">
                            <option value="010" <?php if($sch_field=='010') echo 'selected'?>>010</option>
                            <option value="011" <?php if($sch_field=='011') echo 'selected'?>>011</option>
                            <option value="016" <?php if($sch_field=='016') echo 'selected'?>>016</option>
                            <option value="017" <?php if($sch_field=='017') echo 'selected'?>>017</option>
                            <option value="018" <?php if($sch_field=='018') echo 'selected'?>>018</option>
                            <option value="019" <?php if($sch_field=='019') echo 'selected'?>>019</option>
                            <option value="070" <?php if($sch_field=='070') echo 'selected'?>>070</option>
                        </select>
                        <span>-</span>
                        <input type="text" name="sch_keyword" value="<?=$sch_keyword?>" placeholder="-제외하고 입력">
                        <button type="submit" class="btn go">조회</button>
                    </p>
                </form>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top retWid01">번호</td>
                                <td class="re_top retWid04">구매 방법</td>
                                <td class="re_top retWid04">입금 은행</td>
                                <td class="re_top retWid03">입금액</td>
                                <td class="re_top retWid07">상품권종류</td>
                                <td class="re_top retWid08">권종</td>
                                <td class="re_top retWid09">매수</td>
                                <td class="re_top retWid05">입금자명</td>
                                <td class="re_top retWid03">휴대폰번호</td>
                                <td class="re_top retWid05">신청일</td>
                                <td class="re_top retWid02">처리상태</td>
                                <td class="re_top retWid00">QR 승인</td>
                            </tr>

                            <?php
                            try {

                                $query = "
                                    SELECT seq_no, product_name, chain_price, chain_count, phone_no, user_name, real_price, total_count, res_flag, bank_name, reg_date, buy_type, token FROM shop_user_purchase_list
                                    WHERE phone_no LIKE CONCAT( '%', :sch_keyword, '%')
                                    AND reg_date between :startDate AND DATE_ADD(:endDate, INTERVAL 1 DAY)
                                    ORDER BY reg_date DESC
                                    LIMIT {$startRow}, {$pageSize}
                                ";
                                        
                                $statement = $db->prepare($query);
                                $statement->bindValue(':sch_keyword', $sch_cond);
                                $statement->bindValue(':startDate', $startDate);
                                $statement->bindValue(':endDate', $endDate);
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
                                $product_name = isset($row['product_name']) ? $row['product_name'] : "-";
                                $chain_price = isset($row['chain_price']) ? $row['chain_price'] : "";
                                $chain_count = isset($row['chain_count']) ? $row['chain_count'] : "";
                                $total_count = isset($row['total_count']) ? $row['total_count'] : "";
                                $phone_no = isset($row['phone_no']) ? $row['phone_no'] : "";
                                $user_name = isset($row['user_name']) ? $row['user_name'] : "";
                                $real_price = isset($row['real_price']) ? $row['real_price'] : 0;
                                $bank_name = isset($row['bank_name']) ? $row['bank_name'] : "";
                                $res_flag = isset($row['res_flag']) ? $row['res_flag'] : "";
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : "";
                                $buy_type = isset($row['buy_type']) ? $row['buy_type'] : "-";
                                $token = isset($row['token']) ? $row['token'] : '';

                                $arrChainPrice = explode(",", $chain_price);
                                $chain_price = implode("원<br>", $arrChainPrice);
                                $chain_price .= "원";

                                $arrChainCount = explode(",", $chain_count);
                                $chain_count = implode("장<br>", $arrChainCount);
                                $chain_count .= "장";
                                

                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid retWid01"><?=$seq_no?></td>
                                <td class="re_mid retWid04"><?=strtoupper($buy_type)?></td>
                                <td class="re_mid retWid04"><?=$bank_name?></td>
                                <td class="re_mid retWid03"><?=number_format($real_price)."원"?></td>
                                <!--추가20210719-->
                                <td class="re_mid retWid07"><?=$product_name?></td>
                                <td class="re_mid retWid08"><?=$chain_price?></td>
                                <td class="re_mid retWid09"><?=$chain_count?></td>
                                <td class="re_mid retWid05"><?=$user_name?></td>
                                <td class="re_mid retWid03"><?=$phone_no?></td>
                                <td class="re_mid retDay retWid05"><?=$reg_date?></td>
                                <td class="re_mid retWid02">
                                    <select name="sch_field" id="sch_field" onchange="depositStatus(this)">
                                        <option value="<?='0|'.$seq_no?>" <?php if($res_flag==0) echo "selected"; if($res_flag==1) echo "disabled";?>>미입금</option>
                                        <option value="<?='1|'.$seq_no?>" <?php if($res_flag==1) echo "selected"; ?> disabled >구매완료</option>
                                        <option value="<?='2|'.$seq_no?>" <?php if($res_flag==2) echo "selected"; if($res_flag==1) echo "disabled";?>>입금완료</option>
                                        <option value="<?='3|'.$seq_no?>" <?php if($res_flag==3) echo "selected"; if($res_flag==1) echo "disabled";?>>신청취소</option>
                                    </select>

                                </td>
                                <!--추가 retWid00-->
                                <?php if($res_flag==2 && $buy_type=="qr" && $token=='') { ?>
                                <td class="re_mid retWid00" onclick="sendQR(<?=$seq_no?>)">
                                    <!-- <i class="fa fa-qrcode" aria-hidden="true"></i> -->
                                    <span style="color:red">QR 승인</span>
                                </td>
                                <?php } else if($res_flag==2 && $buy_type=="sms") {?>
                                <td class="re_mid retWid00" onclick="sendSMS(<?=$seq_no?>, '<?=$user_name?>', <?=$real_price?>, '<?=$bank_name?>')">
                                    <!-- <i class="fa fa-comment" aria-hidden="true"></i> -->
                                    <span style="color:red">SMS승인</span>
                                </td>
                                <?php } ?>
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