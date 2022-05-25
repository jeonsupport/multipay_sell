<?php

    include_once "../db_connecter.php";
    include_once "../inc.php";
    include_once "../paging.php";


    $sqlConnecter = new MySQL_Connecter();
    $db = $sqlConnecter->ConnectServer();
    $paging = new Paging();

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $endDate = isset($_GET['dateB']) ? $_GET['dateB'] : date("Y-m-d");
    $startDate = isset($_GET['dateA']) ? $_GET['dateA'] : date("Y-m-d", strtotime("-1 month"));

?>


<!DOCTYPE html>
<html lang="ko">
<?php include('header.php');?>
    <section class="sub_bo_Wrap">
        <div class="subTopBox">
            <div class="subT_SideBox">
                <div class="admin_btn">
                    <p class="btn btn-light"><?=$_SESSION['AdminID']." 님"?></p>
                    <button type="button" class="btn btn-danger" onclick="location.replace('./action/logout.php');">로그아웃</button>
                </div>
                <div class="subtName">
                    <h1>통계</h1>
                    <h2>기간, 매출, 수수료, 상품권 별로 매입 현황을 알 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="statistics">
            <div class="process_sideBox">
                <form class="frm" name="schfrm" id="schfrm" action="./statistics.php">
                    <p class="cell text-center">
                        <input type="date" class="dateA" name="dateA" value="<?=$startDate?>"> ~ 
                        <input type="date" class="dateB" name="dateB" value="<?=$endDate?>">
                        <button type="submit" class="btn go">조회</button>
                    </p>
                </form>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">날짜</td>
                                <td class="re_top">총판매</td>
                                <td class="re_top">해피머니</td>
                                <td class="re_top">도서상품권</td>
                                <td class="re_top">문화상품권</td>
                                <td class="re_top">구글기프트</td>
                                <!-- <td class="re_top">구글카카오</td> -->
                            </tr>

                            <?php
                            try {
                                $query = "
                                        SELECT * FROM 
                                        (SELECT SUM(real_price) AS totalRealPrice, SUM(real_price)/COUNT(real_price) AS totalAverage, SUM(profit_price) AS totalProfit FROM shop_user_purchase_list WHERE res_flag=1 AND date_format(reg_date, '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate') AS t
                                        JOIN
                                        (SELECT SUM(real_price) AS happyTRPrice, SUM(real_price)/COUNT(real_price) AS happyAverage, SUM(profit_price) AS happyProfit FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1006 AND date_format(reg_date, '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate') AS h
                                        JOIN
                                        (SELECT SUM(real_price) AS bookTRPrice, SUM(real_price)/COUNT(real_price) AS bookAverage, SUM(profit_price) AS bookProfit FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1007 AND date_format(reg_date, '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate') AS b
                                        JOIN
                                        (SELECT SUM(real_price) AS cultureTRPrice, SUM(real_price)/COUNT(real_price) AS cultureAverage, SUM(profit_price) AS cultureProfit FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1008 AND date_format(reg_date, '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate') AS c
                                        JOIN
                                        (SELECT SUM(real_price) AS googleTRPrice, SUM(real_price)/COUNT(real_price) AS googleAverage, SUM(profit_price) AS googleProfit FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1023 AND date_format(reg_date, '%Y-%m-%d') BETWEEN '$startDate' AND '$endDate') AS g";
                         
              
                                $statement = $db->prepare($query);
                                $statement->execute();
                                $row = $statement->fetch(PDO::FETCH_ASSOC);
                                $rowCount = $statement->rowCount();

                                $totalRealPrice = isset($row['totalRealPrice']) ? $row['totalRealPrice'] : 0;
                                $qrTRPrice = isset($row['qrTRPrice']) ? $row['qrTRPrice'] : 0;
                                $happyTRPrice = isset($row['happyTRPrice']) ? $row['happyTRPrice'] : 0;
                                $bookTRPrice = isset($row['bookTRPrice']) ? $row['bookTRPrice'] : 0;
                                $cultureTRPrice = isset($row['cultureTRPrice']) ? $row['cultureTRPrice'] : 0;
                                $googleTRPrice = isset($row['googleTRPrice']) ? $row['googleTRPrice'] : 0;

                                $totalAverage = isset($row['totalAverage']) ? $row['totalAverage'] : 0;
                                $qrAverage = isset($row['qrAverage']) ? $row['qrAverage'] : 0;
                                $happyAverage = isset($row['happyAverage']) ? $row['happyAverage'] : 0;
                                $bookAverage = isset($row['bookAverage']) ? $row['bookAverage'] : 0;
                                $cultureAverage = isset($row['cultureAverage']) ? $row['cultureAverage'] : 0;
                                $googleAverage = isset($row['googleAverage']) ? $row['googleAverage'] : 0;

                                $totalProfit = isset($row['totalProfit']) ? $row['totalProfit']: 0;
                                $qrProfit = isset($row['qrProfit']) ? $row['qrProfit']: 0;
                                $happyProfit = isset($row['happyProfit']) ? $row['happyProfit']: 0;
                                $bookProfit = isset($row['bookProfit']) ? $row['bookProfit']: 0;
                                $cultureProfit = isset($row['cultureProfit']) ? $row['cultureProfit']: 0;
                                $googleProfit = isset($row['googleProfit']) ? $row['googleProfit']: 0;
             

                                if($rowCount==0) { throw new Exception("게시물이 없습니다."); }

                            }catch(Exception $e) {
                                echo $e->getMessage();
                            }
                            
                            ?>
                            <tr class="rem_tr">
                                <td class="re_mid">총합</td>
                                <td class="re_mid"><?=number_format($totalRealPrice)?></td>
                                <td class="re_mid"><?=number_format($happyTRPrice)?></td>
                                <td class="re_mid"><?=number_format($bookTRPrice)?></td>
                                <td class="re_mid"><?=number_format($cultureTRPrice)?></td>
                                <td class="re_mid"><?=number_format($googleTRPrice)?></td>
                                <!-- <td class="re_mid">0</td> -->
                            </tr>
                            <tr class="rem_tr">
                                <td class="re_mid">평균</td>
                                <td class="re_mid"><?=number_format($totalAverage)?></td>
                                <td class="re_mid"><?=number_format($happyAverage)?></td>
                                <td class="re_mid"><?=number_format($bookAverage)?></td>
                                <td class="re_mid"><?=number_format($cultureAverage)?></td>
                                <td class="re_mid"><?=number_format($googleAverage)?></td>
                                <!-- <td class="re_mid">0</td> -->
                            </tr>
                            <tr class="rem_tr">
                                <td class="re_mid">순이익</td>
                                <td class="re_mid"><?=number_format($totalProfit)?></td>
                                <td class="re_mid"><?=number_format($happyProfit)?></td>
                                <td class="re_mid"><?=number_format($bookProfit)?></td>
                                <td class="re_mid"><?=number_format($cultureProfit)?></td>
                                <td class="re_mid"><?=number_format($googleProfit)?></td>
                                <!-- <td class="re_mid">0</td> -->
                            </tr>
                            <?php

                            $dayCount = ( strtotime($endDate) - strtotime($startDate) ) / 86400;


                            //페이징
                            $pageSize = 40;
                            $startRow = ($page-1) * $pageSize;
                            $url = $_SERVER['PHP_SELF'];

                            try {
                            
                                $config = array(
                                'base_url' => $url,
                                'page_rows' => $pageSize,
                                'total_rows' => $dayCount
                                );
                            
                                $paging->initialize($config);
                                $pagination = $paging->create();
                            
                            } catch(Exception $e) {
                                die($e->getMessage());
                            }

                            if ($dayCount < 0) {
                                $dayCount = -1;
                            }
                            else{
                                $query = "SELECT * FROM (";
                            }
                            $tDate = $endDate;
                            for ($i=0;$i<$dayCount+1;$i++)
                            {
                                $query .= "
                                (SELECT '$tDate' AS tableDate) AS d JOIN
                                (SELECT COALESCE(sum(real_price),0) AS totalRealPrice FROM shop_user_purchase_list WHERE res_flag=1 AND date_format(reg_date, '%Y-%m-%d') = '$tDate') AS t JOIN
                                (SELECT COALESCE(sum(real_price),0) AS happyTRPrice FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1006 AND date_format(reg_date, '%Y-%m-%d') = '$tDate') AS h JOIN
                                (SELECT COALESCE(sum(real_price),0) AS bookTRPrice FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1007 AND date_format(reg_date, '%Y-%m-%d') = '$tDate') AS b JOIN
                                (SELECT COALESCE(sum(real_price),0) AS cultureTRPrice FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1008 AND date_format(reg_date, '%Y-%m-%d') = '$tDate') AS c JOIN
                                (SELECT COALESCE(sum(real_price),0) AS googleTRPrice FROM shop_user_purchase_list WHERE res_flag=1 AND product_no=1023 AND date_format(reg_date, '%Y-%m-%d') = '$tDate') AS g";
                                
                                $tDate = date("Y-m-d", strtotime($tDate."-1day"));

                                if($dayCount <= $i) break;
                                $query .= ") UNION SELECT * FROM (";
                            }
                            if ($dayCount > -1) {
                                $query .= ")";
                                $query .= " LIMIT {$startRow}, {$pageSize}";
                                $statement = $db->prepare($query);
                                $statement->execute();                                
                            }

                            
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)){
                                $tableDate = isset($row['tableDate']) ? $row['tableDate'] : "";
                                $totalRealPrice = isset($row['totalRealPrice']) ? $row['totalRealPrice'] : 0;
                                $qrTRPrice = isset($row['qrTRPrice']) ? $row['qrTRPrice'] : 0;
                                $happyTRPrice = isset($row['happyTRPrice']) ? $row['happyTRPrice'] : 0;
                                $bookTRPrice = isset($row['bookTRPrice']) ? $row['bookTRPrice'] : 0;
                                $cultureTRPrice = isset($row['cultureTRPrice']) ? $row['cultureTRPrice'] : 0;
                                $googleTRPrice = isset($row['googleTRPrice']) ? $row['googleTRPrice'] : 0;

                            ?>
                            <tr class="rem_tr">
                                <td class="re_mid"><?=$tableDate?></td>
                                <td class="re_mid"><?=number_format($totalRealPrice)?></td>
                                <td class="re_mid"><?=number_format($happyTRPrice)?></td>
                                <td class="re_mid"><?=number_format($bookTRPrice)?></td>
                                <td class="re_mid"><?=number_format($cultureTRPrice)?></td>
                                <td class="re_mid"><?=number_format($googleTRPrice)?></td>
                                <!-- <td class="re_mid">0</td> -->
                            </tr>
                            <?php
                            }?>
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