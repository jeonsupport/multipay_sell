<?php

    include_once "db_connecter.php";
    include_once "paging.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();
    $paging = new Paging();


    // 파라미터
    $page      = isset($_GET['page'])      ? $_GET['page']      : 1;
    $p_product   = isset($_GET['product'])   ? $_GET['product']   : '';
    $p_amount    = isset($_GET['amount'])    ? $_GET['amount']    : '';
    $p_price     = isset($_GET['price'])     ? $_GET['price']     : '';
    $p_bank_name = isset($_GET['bank_name']) ? $_GET['bank_name'] : '';
    $p_name      = isset($_GET['name'])      ? $_GET['name']      : '';
    $p_phone_no  = isset($_GET['phone_no'])  ? $_GET['phone_no']  : '';
    $param = urldecode($_SERVER['QUERY_STRING']);
    
    // 페이징
    $pageSize = 20;
    $startRow = ($page-1) * $pageSize;
    $url = $_SERVER['PHP_SELF'];

    try {


        $query = 
            "
                SELECT seq_no AS cnt FROM shop_user_purchase_list
                WHERE product_no = :product_no
                AND total_count = :total_count
                AND real_price = :real_price
                AND bank_name = :bank_name
                AND user_name = :user_name
                AND phone_no = :phone_no
                AND res_flag = 2
                AND token IS NOT NULL
            ";

        $statement = $db->prepare($query);
        $statement->bindValue(':product_no', $p_product);
        $statement->bindValue(':total_count', $p_amount);
        $statement->bindValue(':real_price', $p_price);
        $statement->bindValue(':bank_name', $p_bank_name);
        $statement->bindValue(':user_name', $p_name);
        $statement->bindValue(':phone_no', $p_phone_no);
        $statement->execute();
        $totRecord = $statement->rowCount();
    
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
<section class="sub_bo_Wrap">
    <div class="subT_SideBox">
        <div class="subtName">
            <h1>구매조회</h1>
            <h2>검색하시면 구매조회를 확인할 수 있습니다</h2>
        </div>
    </div>
    <div id="process_submit">
        <div class="process_sideBox">
            <div class="top_phoneBox">
                <form class="frm" name="schfrm" id="schfrm" action="<?=$_SERVER['PHP_SELF']?>">
                    <p class="cell text-center">
                        <select name="product">
                            <option value="">상품권종류</option>
                            <?php
                                $query = "SELECT product_no, product_name FROM shop_product_list WHERE use_flag = 1 ORDER BY reg_date DESC";
                                $statement = $db->query($query);
                                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {

                                    $product_no = isset($row['product_no']) ? $row['product_no'] : '';
                                    $product_name = isset($row['product_name']) ? $row['product_name'] : '';
                                
                            ?>
                            <option value="<?=$product_no?>" <?php if($p_product==$product_no) echo "selected='selected'"; ?>><?=$product_name?></option>
                            <?php } // end while ?>
                        </select>
                        <input type="text" name="amount" value="<?=$p_amount?>" placeholder="구매장수" >
                        <input type="text" name="price" value="<?=$p_price?>" placeholder="구매금액" >
                        <select name="bank_name">
                            <option value="">입금은행</option>
                            <?php
                                $query = "SELECT bank_name FROM shop_admin_account_list ORDER BY user_count ASC";
                                $statement = $db->query($query);
                                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                    $bank_name = isset($row['bank_name']) ? $row['bank_name'] : '';
                            ?>
                            <option value="<?=$bank_name?>" <?php if($p_bank_name==$bank_name) echo "selected='selected'" ?>><?=$bank_name?></option>
                            <?php } // end while ?>
                        </select>
                        <input type="text" name="name" value="<?=$p_name?>" placeholder="입금자명">
                        <input type="text" name="phone_no" value="<?=$p_phone_no?>" maxlength="13" placeholder="휴대폰번호">
                        <button type="submit" class="btn go">조회</button>
                    </p>
                </form>
            </div>
            <div class="oi_inputArea">
                <div class="recentTableBox">
                    <table>
                        <tr class="ret_tr">
                            <td class="re_top retWid01">번호</td>
                            <td class="re_top retWid06">입금액</td>
                            <td class="re_top retWid04">입금자명</td>
                            <td class="re_top retWid03">휴대폰번호</td>
                            <td class="re_top retWid05">신청일</td>
                            <td class="re_top retWid02">처리상태</td>
                        </tr>
                        
                        <?php
                            try {                                        
                            
                                $query = "
                                    SELECT seq_no, phone_no, user_name, real_price, res_flag, reg_date, buy_type FROM shop_user_purchase_list
                                    WHERE product_no = :product_no
                                    AND total_count = :total_count
                                    AND real_price = :real_price
                                    AND bank_name = :bank_name
                                    AND user_name = :user_name
                                    AND phone_no = :phone_no
                                    AND res_flag = 2
                                    AND token IS NOT NULL
                                    ORDER BY reg_date DESC
                                    LIMIT {$startRow}, {$pageSize}
                                ";
                                
                                $statement = $db->prepare($query);
                                $statement->bindValue(':product_no', $p_product);
                                $statement->bindValue(':total_count', $p_amount);
                                $statement->bindValue(':real_price', $p_price);
                                $statement->bindValue(':bank_name', $p_bank_name);
                                $statement->bindValue(':user_name', $p_name);
                                $statement->bindValue(':phone_no', $p_phone_no);
                                $statement->execute();
                                $rowCount = $statement->rowCount();

                            }catch(PDOException $e) {
                                echo $e->getMessage();
                            }


                            $rcnt = 0;
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                
                                $rcnt++;
                                $no = $rcnt + $startRow;
                                $seq_no     = isset($row['seq_no'])     ? $row['seq_no'] : "";
                                $phone_no   = isset($row['phone_no'])   ? $row['phone_no'] : "";
                                $user_name  = isset($row['user_name'])  ? preg_replace('/.(?=.$)/u','*',$row['user_name']) : "";
                                $real_price = isset($row['real_price']) ? number_format($row['real_price']) : "";
                                $res_flag   = isset($row['res_flag'])   ? $row['res_flag'] : "";
                                $reg_date   = isset($row['reg_date'])   ? substr($row['reg_date'], 0, 10) : "";
                                $buy_type   = isset($row['buy_type'])   ? $row['buy_type'] : "";

                                $phoneLen = strlen($phone_no);
                                $phone_no = substr_replace($phone_no, "****", $phoneLen-5, 4);

                                $resClass = "";
                                $resClassText = "";
                                if($res_flag == 0) {
                                    $resClass = "gift_01 gi_bg04";
                                    $resClassText = "미입금";
                                } else if($res_flag == 1) {
                                    $resClass = "gift_01 gi_bg02";
                                    $resClassText = "구매완료";
                                } else if($res_flag == 2) {
                                    $resClass = "gift_01 gi_bg01";
                                    $resClassText = "입금완료";
                                } else {
                                    $resClass = "gift_01 gi_bg04";
                                    $resClassText = "신청취소";
                                }
                        ?>

                        <tr class="rem_tr">
                            <td class="re_mid retWid01"><?=$no?></td>
                            <td class="re_mid retWid06">
                                <?php if($buy_type == 'qr') { ?>
                                <a href="process_detail.php?<?=$param?>&page=<?=$page?>&seq_no=<?=$seq_no?>">
                                <?=$real_price."원"?>
                                <!-- <i class="fa fa-lock" aria-hidden="true"></i> -->
                                <?php } else { ?>
                                <?=$real_price."원"?>
                                <?php }?>
                            </td>
                            <td class="re_mid retWid04"><?=$user_name?></td>
                            <td class="re_mid retWid03"><?=$phone_no?></td>
                            <td class="re_mid retDay retWid05"><?=$reg_date?></td>
                            <td class="re_mid retWid02"><div class='<?=$resClass?>'><?=$resClassText?></div></td>
                        </tr>
                        
                        <?php }?>

                    </table>
                    <div class="d-flex justify-content-center"><?=$pagination?></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include('layout/footer.php');?>
</html>