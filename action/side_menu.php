<?php

    include_once "../db_connecter.php";

    $conn = new MySQL_Connecter();
    $db_access = $conn->ConnectServer();

    $jData  = isset($_POST['sendData']) ? $_POST['sendData'] : "";
    $productNo = isset($jData['productNo']) ? strip_tags($jData['productNo']) : $errMsg .= "productNo; ";

    try {

        // ------------------------------------------------------------------------------------------
        // 사이드 메뉴 ajax 처리
            $query = "
                    SELECT A.product_no, A.price, B.discount_comm_rate
                    FROM shop_product_use_list AS A
                    JOIN shop_product_list AS B
                    ON A.product_no = B.product_no
                    WHERE A.use_flag = 1
                    AND B.use_flag = 1
                    AND A.product_no = $productNo
                    AND B.product_no = $productNo
                    AND A.price <= (SELECT balance FROM shop_balance WHERE use_state = 1)
                    ORDER BY A.price DESC
                ";


            $statement = $db_access->prepare($query);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $rowCount = $statement->rowCount();

            $rate = isset($row['discount_comm_rate']) ? $row['discount_comm_rate'] : '';

            if($rowCount==0) {
                throw new Exception("상품권 재고가 없습니다.");
            }

            
            $strStartHTML = "";
            $strStartHTML .= "<div id='basket' class='resultSide selected'>";
            $strStartHTML .= "<div class='sideWrap'>";
            // $strStartHTML .= "<h2 class='resultHead'>상품권 선택</h2>";
            $strStartHTML .= "<div class='emt'></div>";
            $strStartHTML .= "<div class='resultBox selected'>";
            // 211102추가 구매방법  
            $strStartHTML .= "<div class='resultSection'>";
            $strStartHTML .= "<h3 class='sectionHead p01'>구매 방법</h3>";
            $strStartHTML .= "<h3 class='sectionHead m01'>상품권 리스트</h3>";
            $strStartHTML .= "<ul class='addList'>";
            $strStartHTML .= "<li class='payment'>";
            $strStartHTML .= "<div class='chkForm'><input type='radio' id='buy_type1' name='buy_type' value='qr' checked onclick='javascript:basket.buy_type(1);'> <label for='buy_type1' class='label'><span>QR 구매</span><a class='notice' href='guide_qr.php'>QR구매란?</a>";
            $strStartHTML .= "</label> <label for='buy_type1' class='chkLabel'></label></div>";
            $strStartHTML .= "</li>";

            if($productNo != 1001) { // 멀티캐시 sms 사용 x
                $strStartHTML .= "<li class='payment'>";
                $strStartHTML .= "<div class='chkForm'><input type='radio' id='buy_type2' name='buy_type' value='sms' disabled onclick='javascript:basket.buy_type(2);'> <label for='buy_type2' class='label'><span>SMS 구매</span>";
                $strStartHTML .= "</label> <label for='buy_type2' class='chkLabel'></label></div>";
                $strStartHTML .= "</li>";
            }
            
            $strStartHTML .= "</ul>";
            $strStartHTML .= "</div>";

            $strStartHTML .= "<div class='resultSection'>";
            $strStartHTML .= "<h3 class='sectionHead'>결제 수단</h3>";
            $strStartHTML .= "<ul class='addList'>";
            $strStartHTML .= "<li class='payment'>";
            $strStartHTML .= "<div class='chkForm'><input type='radio' id='payment_type1' name='payment_type' value='type1' checked disabled> <label for='payment_type1' class='label'><span>무통장입금</span>";
            $strStartHTML .= "</label> <label for='payment_type1' class='chkLabel'></label></div>";
            $strStartHTML .= "</li>";
            $strStartHTML .= "</ul>";
            $strStartHTML .= "</div>";
            // 220311 은행 추가
            // $strStartHTML .= "<div class='resultSection bank'>";
            // $strStartHTML .= "<h3 class='sectionHead'>은행 선택</h3>";
            // $strStartHTML .= "<ul class='addList'>";
            // $strStartHTML .= "<li class=''>";
            // $strStartHTML .= "<select name='bankName'>";
            // // option 반복문
            // $query_1 = "SELECT bank_name FROM shop_admin_account_list ORDER BY user_count ASC";
            // $statement = $db_access->query($query_1);
            // while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            //     $bank_name = isset($row['bank_name']) ? $row['bank_name'] : '';

            //     $strStartHTML .= "<option value='$bank_name'>$bank_name</option>";
            // }
            // $strStartHTML .= "</select>";
            // $strStartHTML .= "</li>";
            // $strStartHTML .= "</ul>";
            // $strStartHTML .= "</div>";
            $strStartHTML .= "<div class='resultSection type data'>";
            $strStartHTML .= "<h3 class='sectionHead'>권종 선택</h3>";
            $strStartHTML .= "<ul class='addList'>";

            $strEndHTML = "";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<div class='resultSection inm'>";
            $strEndHTML .= "<h3 class='sectionHead'>입금자 정보</h3>";
            $strEndHTML .= "<ul class='addList'>";
            
            // 220315 은행 위치 이동
            $strEndHTML .= "<li><span class='star'>*</span>";
            $strEndHTML .= "<select name='bankName'>";
            $strEndHTML .= "<option value=''>입금은행을 선택하세요</option>";

            // option 반복문
            $query_1 = "SELECT bank_name FROM shop_admin_account_list ORDER BY user_count ASC";
            $statement = $db_access->query($query_1);
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $bank_name = isset($row['bank_name']) ? $row['bank_name'] : '';
                $strEndHTML .= "<option value='$bank_name'>$bank_name</option>";
            }
            $strEndHTML .= "</select>";
            $strEndHTML .= "</li>";
            //
            $strEndHTML .= "<li><span class='star'>*</span><input type='tel' id='use_tel' placeholder='휴대폰번호(핀번호발송과 입금현황조회에 사용됩니다)' class='use_tel phoneNumber' oninput='maxLengthCheck(this)' maxlength='13'></li>";
            $strEndHTML .= "<li><span class='star'>*</span><input type='text' id='use_account_name' placeholder='입금자명(입금확인을 위해 사용됩니다)' class='use_account_name' oninput='maxLengthCheck(this)'' maxlength='6'></li>";
            // $strEndHTML .= "<li class='prdResult'>";
            // $strEndHTML .= "<p class='title'>입금액(할인 ".$rate."%)</p>";
            // $strEndHTML .= "<p class='price'><b id='sum_p_price3'>0</b>원</p>";
            // $strEndHTML .= "</li>";
            $strEndHTML .= "</ul>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<div class='totalBox'>";
            $strEndHTML .= "<div class='prdResult'>";
            $strEndHTML .= "<p class='title'>입금액(할인 ".$rate."%)</p>";
            $strEndHTML .= "<p class='price'><b id='sum_p_price3'>0</b>원</p>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<div class='prdResult'>";
            $strEndHTML .= "<p class='title'>구매 장수</p>";
            $strEndHTML .= "<p class='price'>+<b id='sum_p_num'>0</b>장</p>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<div class='prdResult cb'>";
            $strEndHTML .= "<b class='title'>총 구매 금액</b>";
            $strEndHTML .= "<p class='price'><b id='sum_p_price2'>0</b>원</p>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<button type='button' class='btnBuy' onclick='javascript:basket.event_btnBuy();'>구매하기</button>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "<div class='btnClose' onclick='closePop(1);'><i class='fa fa-angle-left' aria-hidden='true'></i>이전</div>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "</div>";
            $strEndHTML .= "</ul>";
            $strEndHTML .= "<input type='hidden' id='rate' value='$rate' />";
                

            $num = 0;
            $strAmountHTML = "";
            $statement = $db_access->prepare($query);
            $statement->execute();
            while($row=$statement->fetch(PDO::FETCH_ASSOC)) { 

                $price = isset($row['price']) ? $row['price'] : "";
                $num++;

                $priceLen = strlen($price);
                $strShowPrice = "";
                if($priceLen==6) {
                    $strShowPrice = substr($price, 0, 2)."만원권";
                } else if($priceLen==5) { 
                    $strShowPrice = substr($price, 0, 1)."만원권";
                } else {
                    $strShowPrice = substr($price, 0, 1)."천원권";
                }

                // if($price==1000) continue; // 1000원 제거시 주석해제

                $strAmountHTML .= "<li class='list'>";
                $strAmountHTML .= "<p class='prdName'><input type='hidden' name='p_price' id='p_price$num' class='p_price' value='$price'><span></span> $strShowPrice</p>";
                $strAmountHTML .= "<p class='prdPrice'><b class='sale sum'>0원</b></p>";
                $strAmountHTML .= "<div class='control num'>";
                $strAmountHTML .= "<div class='amount updown'>";
                $strAmountHTML .= "<input type='text' id='p_num$num' class='qty_count p_num' name='p_num$num' value='0' onkeyup='javascript:basket.changePNum($num);' >";
                $strAmountHTML .= "<button class='qty_btns minus' onclick='javascript:basket.changePNum($num);'>";
                $strAmountHTML .= "<i class='fa fa-minus' aria-hidden='true'></i>";
                $strAmountHTML .= "</button>";
                $strAmountHTML .= "<button class='qty_btns plus' onclick='javascript:basket.changePNum($num);'>";
                $strAmountHTML .= "<i class='fa fa-plus' aria-hidden='true'></i>";
                $strAmountHTML .= "</button>";
                $strAmountHTML .= "</div>";
                $strAmountHTML .= "</div>";
                $strAmountHTML .= "</li>";

            }

            $result_array = array(
                'status' => '1',
                'sideMenu' => $strStartHTML.$strAmountHTML.$strEndHTML
            );

            echo parseJson($result_array);
            return;

    } catch(Exception $e) {
        echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    

    function parseJson($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
    }