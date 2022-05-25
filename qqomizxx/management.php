<?php
    include_once "../db_connecter.php";
    include_once "../inc.php";
    
    $sqlConnecter = new MySQL_Connecter();
    $db = $sqlConnecter->ConnectServer();
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
                    <h1>상품권 관리</h1>
                    <h2>사이트에 판매할 상품권을 관리할 수 있습니다</h2>
                </div>
            </div>
        </div>
        <div id="process_submit" class="mngment">
            <div class="process_sideBox">
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">번호</td>
                                <td class="re_top">상품권코드</td>
                                <td class="re_top">상품권명</td>
                                <td class="re_top">할인율</td>
                                <td class="re_top">발행사수수료</td>
                                <td class="re_top">수정일</td>
                                <td class="re_top">등록일</td>
                                <td class="re_top">웹목록</td>
                            </tr>

                            <?php
                            try {
                                $query = "SELECT * from shop_product_list ORDER BY reg_date DESC";
                                $statement = $db->prepare($query);
                                $statement->execute();
                                $rowCount = $statement->rowCount();
                            
                                if($rowCount == 0) {
                                    throw new Exception("데이터가 없습니다.");
                                }


                            } catch(Exception $e) {
                                echo $e->getMessage();
                            }
                            

                            $rcnt = 0;
                            while($row=$statement->fetch(PDO::FETCH_ASSOC)) {

                                $rcnt++;

                                $no = $rcnt;
                                $seq_no = isset($row['seq_no']) ? $row['seq_no'] : '';
                                $product_no = isset($row['product_no']) ? $row['product_no'] : '';
                                $product_name = isset($row['product_name']) ? $row['product_name'] : '';
                                $discount_comm_rate = isset($row['discount_comm_rate']) ? $row['discount_comm_rate'] : '';
                                $publisher_comm_rate = isset($row['publisher_comm_rate']) ? $row['publisher_comm_rate'] : '';
                                $use_flag = isset($row['use_flag']) ? $row['use_flag'] : '';
                                $upd_date =  isset($row['upd_date']) ? $row['upd_date'] : '-';
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : '';

                                $check = $seq_no.'|1';
                                $n_check = $seq_no.'|0';
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid"><?=$no?></td>
                                <td class="re_mid"><?=$product_no?></td>
                                <td class="re_mid" style="text-decoration: underline; text-underline-position: under;"><a href="management_pop.php?seq_no=<?=$seq_no?>&product_no=<?=$product_no?>" onclick="window.open(this.href,'','left=300,top=200,width=500,height=500'); return false;"><?=$product_name?></a></td>
                                <td class="re_mid"><?=$discount_comm_rate?>%</td>
                                <td class="re_mid"><?=$publisher_comm_rate?>%</td>
                                <td class="re_mid"><?=$upd_date?></td>
                                <td class="re_mid"><?=$reg_date?></td>
                                <td class="re_mid">
                                    <input
                                        type='radio'
                                        <?php if($use_flag==1) echo "checked"; ?>
                                        value=<?=$check?>
                                        id=<?='show'.$no?>
                                        onchange="upWebState(this)">
                                    <label for=<?='show'.$no?>>표시</label>
                                    <input
                                        type='radio'
                                        <?php if($use_flag==0) echo "checked"; ?>
                                        value=<?=$n_check?>
                                        id=<?='hide'.$no?>
                                        onchange="upWebState(this)">
                                    <label for=<?='hide'.$no?>>숨김</label>
                                </td>
                            </tr>

                            <?php } ?>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="assets/js/basket.js"></script>
</body>
</html>