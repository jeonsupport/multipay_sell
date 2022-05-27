<?php
    session_start();

    include_once "db_connecter.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

?>

<!DOCTYPE html>
<html lang="ko">
<?php include('layout/header.php');?>
<?php include('layout/pop.php');?>
<main>
    <header class="header shop">
        <div class="inner">
            <ul>
                <li><a href="">전체</a></li>
                <li><a href="">문화상품권</a></li>
                <li><a href="">해피머니</a></li>
                <li><a href="">도서상품권</a></li>
                <li><a href="">스마트문상</a></li>
                <li><a href="">온캐시</a></li>
                <li><a href="">퍼니카드</a></li>
                <li><a href="">넥슨카드</a></li>
                <li><a href="">구글플레이카드</a></li>
                <li><a href="">카카오코인</a></li>
            </ul>
        </div>
    </header>
    <div id="GiftList" class="shop2">
        <section id="container">
            <div class="innerWidth">
                <div class="productWrap">
                    <h2 class="productTitle"></h2>

                    <div class="productPop outputBox">
                        <!-- <h2>입금 내역</h2> -->
                        <div class="outputBoxTop">
                            <img src="assets/img/ad/cont01_02.png">
                            <h3>입금 계좌 안내<br><span>BANK INFO</span></h3>
                        </div>
                        <table>
                            <tr>
                                <th>입금액</th>
                                <td id="pop_totPrice"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>입금자명</th>
                                <td id="pop_userName"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>입금은행</th>
                                <td id="bankName"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>계좌번호</th>
                                <td>
                                    <input id="clipInput" value="" readonly>
                                </td>
                                <!-- <td><span onclick="copy_to_clip()"><i class="fa fa-clone" aria-hidden="true"></i></span></td> -->
                                <td><span onclick="copy_to_clip()">복사하기</span></td>
                            </tr>
                            <tr>
                                <th>예금주</th>
                                <td id="pop_account_holder">김미경 (바우처이지패스)</td>
                                <td></td>
                            </tr>
                        </table>
                        <div id="pop_qrimg"></div>
                        <button class="pop_btn" type="button" id="buy_cancel_btn" onclick="depositConfirm()">구매취소</button>

                        <!-- <ul> -->
                            <!-- <li>아래의 입금정보를 확인하신 후, <br><span class='highlighter01'>계좌로 입금</span>해주시기 바랍니다.</li> -->
                            <!-- <li>- 입금액<span id="pop_totPrice"></span></li> -->
                            <!-- <li>- 입금자명<span id="pop_userName"></span></li> -->
                            <!-- <li>- 입금은행<span id="bankName"></span></li> -->
                            <!-- <li>- 계좌번호 -->
                                <!-- <span class="clip"> -->
                                    <!-- <input id="clipInput" value="" readonly> -->
                                    <!-- <span onclick="copy_to_clip()"><i class="fa fa-clone" aria-hidden="true"></i></span> -->
                                <!-- </span> -->
                            <!-- </li> -->
                            <!-- <li>- 예금주<span id="pop_account_holder">김미경 (바우처이지패스)</span></li> -->
                            <!-- <span id="pop_qrimg"></span> -->
                            
                            <!--//210714 미활성화
                            <li>- 입금유효기간<span>2021년 7월 20일</span></li>-->
                            <!-- <li class="check"><button type="button" onclick="depositConfirm()">확인</button></li> -->
                        <!-- </ul> -->
                    </div>
                    <div class="productList">
                        <ul class="prd">

                            <?php

                                $query = "
                                            select a.product_no, a.product_name, a.publisher_name, sum(b.price) as total_price
                                            from shop_product_list as a
                                            join shop_product_use_list as b
                                            on a.product_no = b.product_no
                                            where a.use_flag = 1
                                            and b.use_flag = 1
                                            group by a.product_no, a.product_name, a.publisher_name
                                        ";

                                $statement = $db->prepare($query);
                                $statement->execute();
                                $rowCount = $statement->rowCount();

                                $strNotData = "";
                                $strNotData .= "<li class='blank'>";
                                $strNotData .= "<div class='infor'>";
                                // $strNotData .= "<div class='thumb'><img src='assets/img/basket/noimg2.png' alt=''></div>";
                                $strNotData .= "</div>";
                                $strNotData .= "</li>";
                                if($rowCount==0) {
                                    
                                    for($i=0; $i<3; $i++) {
                                        echo $strNotData;
                                    }
                                }
                        
                                $rcnt=0;
                                while($row=$statement->fetch(PDO::FETCH_ASSOC)) {
                                    $rcnt++;
                                    $product_no = isset($row['product_no']) ? $row['product_no'] : "";
                                    $product_name = isset($row['product_name']) ? $row['product_name'] : "";
                                    $publisher_name = isset($row['publisher_name']) ? $row['publisher_name'] : "";
                                    $total_price = isset($row['total_price']) ? $row['total_price'] : 0;

                                    if ($total_price < 1000) { // 1000원 이하일 경우 표시 x
                                        continue;
                                    }
                                    if($product_no == 1023) {
                                        $product_name = "구글기프트카드";
                                    }
                                    
                                    $strListHTML = "";
                                    $strListHTML .= "<li>";
                                    // $strListHTML .= "<div class='ic_online'>온라인전용</div>";
                                    // $strListHTML .= "<div class='ic_qr'><img src='assets/img/ic_qr.png'></div>";
                                    $strListHTML .= "<div class='infor'>";
                                    $strListHTML .= "<div class='thumb'><img src='assets/img/basket/$product_no.png' class='$product_no'></div>";
                                    $strListHTML .= "<p class='subj'><img src='assets/img/i_logo_multi.png'>$product_name<br><span>$publisher_name</span></p>";
                                    $strListHTML .= "</div>";
                                    $strListHTML .= "</li>";

                                    echo $strListHTML;
                                }

                                if($rcnt<3) {
                                    for($i=0; $i<3-$rcnt; $i++) {
                                        echo $strNotData;
                                    }
                                }
                                
                            ?>

                        </ul>
                    </div>
                </div>
                <div id="basketEmpty" class="resultSide notSelected">
                    <div class="sideWrap">
                        <div class="resultBox notSelected">
                            <div class="notSelectedMsg">
                                <i class="fa fa-exclamation-circle fa-3x" aria-hidden="true"></i>
                                <p class="msg">상품권 종류를 선택해주세요<br>클릭하면 창이 열립니다</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 본인인증 -->
                <form name="frmPopup" method="post">
                    <input type="hidden" name="user_no" value="">
                </form>
                <div id="sideMenu" class="pop1"></div>
            </div>
        </section>
    </div>
</main>
<?php include('layout/footer.php');?>
</html>