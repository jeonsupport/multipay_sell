<?php
    include_once "../db_connecter.php";
    include_once "../inc.php";
    
    $sqlConnecter = new MsSQL_Connecter();
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
        <div id="process_submit">
            <div class="process_sideBox">
                <div class="top_phoneBox">
                    <p class="cell text-center">
                        <!-- 추가 버튼 -->
                        <button class="btn manageBtn" type="button" onclick="pop_up();">가맹점 생성</button>
                    </p>
                </div>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top"><input type="checkbox" onclick="chkAll('idx[]', this.checked);" /></td>
                                <td class="re_top">번호</td>
                                <td class="re_top">가맹점명</td>
                                <td class="re_top">개인키</td>
                                <td class="re_top">무료사용신청일</td>
                                <td class="re_top">유료사용신청일</td>
                                <td class="re_top">무료사용마감일</td>
                                <td class="re_top">유료사용마감일</td>
                                <td class="re_top">수정날짜</td>
                                <td class="re_top">등록날짜</td>
                                <td class="re_top">잠금설정</td>
                            </tr>

                            <?php
                            try {
                                $query = "SELECT SeqNo, ChainName, Token, Price, FreeYMD, PayYMD, PayState, Lock, EndFreeMonth, EndPayMonth, UpdDate, RegDate FROM A_BuySiteAPIControl ORDER BY RegDate DESC";
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
                                $seq_no = isset($row['SeqNo']) ? $row['SeqNo'] : '';
                                $chain_name = isset($row['ChainName']) ? $row['ChainName'] : '';
                                $token = isset($row['Token']) ? $row['Token'] : '';
                                $price = isset($row['Price']) ? $row['Price'] : '';
                                $free_ymd = isset($row['FreeYMD']) ? $row['FreeYMD'] : '';
                                $pay_ymd = isset($row['PayYMD']) ? $row['PayYMD'] : '-';
                                $pay_state = isset($row['PayState']) ? $row['PayState'] : '';
                                $lock  = isset($row['Lock'])  ? $row['Lock']  : '';
                                $end_free_month = isset($row['EndFreeMonth']) ? $row['EndFreeMonth'] : '';
                                $end_pay_month = isset($row['EndPayMonth']) ? $row['EndPayMonth'] : '';
                                $upd_date = isset($row['UpdDate']) ? $row['UpdDate'] : '-';
                                $reg_date = isset($row['RegDate']) ? $row['RegDate'] : '';

                                $check = $seq_no.'|1';
                                $n_check = $seq_no.'|0';
                        ?>

                            <tr class="rem_tr">
                                <td class="re_mid"><input type="checkbox" name="idx[]" value="<?=$seq_no?>" /></td>
                                <td class="re_mid"><?=$no?></td>
                                <td class="re_mid" style="text-decoration: underline; text-underline-position: under;"><a href="api_control_pop.php?seq_no=<?=$seq_no?>" onclick="window.open(this.href,'','left=300,top=200,width=500,height=500'); return false;"><?=$chain_name?></a></td>
                                <td class="re_mid"><?=$token?></td>
                                <td class="re_mid"><?=$free_ymd?></td>
                                <td class="re_mid"><?=$pay_ymd?></td>
                                <td class="re_mid">+ <?=$end_free_month?> 개월</td>
                                <td class="re_mid">+ <?=$end_pay_month?> 개월</td>
                                <td class="re_mid"><?=$upd_date?></td>
                                <td class="re_mid"><?=$reg_date?></td>
                                <td class="re_mid">
                                    <input
                                        type='radio'
                                        <?php if($lock==0) echo "checked"; ?>
                                        value=<?=$n_check?>
                                        id=<?='show'.$no?>
                                        onchange="lock(this)">
                                    <label for=<?='show'.$no?>>사용</label>
                                    <input
                                        type='radio'
                                        <?php if($lock==1) echo "checked"; ?>
                                        value=<?=$check?>
                                        id=<?='hide'.$no?>
                                        onchange="lock(this)">
                                    <label for=<?='hide'.$no?>>잠금</label>
                                </td>
                            </tr>
                        <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
<script>
    function post_to_url(path, params, method) {
        method = method || "post"; 
        var form = document.createElement("form");
        form.setAttribute("method", method);
        form.setAttribute("action", path);
        for(var key in params) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
        }
        document.body.appendChild(form);
        form.submit();
    }


    function sel_del(){
        var f = document.hform;
        var cart = "";
        var obj = document.getElementsByName("idx[]");
        for(var i=0; i<obj.length; i++){
            if(obj[i].checked) cart += obj[i].value + ",";
        }
        cart = cart.substr(0, cart.length-1);
        if(!cart){
            alert("삭제할 데이터를 선택해주세요");
            return;
        }
        if(confirm("정말 삭제하시겠습니까?") == true){
           post_to_url('./api_control_action.php', {'cart':cart, 'action':'delete'});
        } else{
            return;
        }
    }

    function pop_up() {
        window.open('./api_control_pop.php?action=write','','left=300,top=200,width=500,height=200');
    }

    function lock(e) {
        if(!confirm("처리 상태를 변경하시겠습니까?")) {
            alert("취소하였습니다.");
            location.reload();
        }
        else {
   
            let form_data = {
                action: 'lock', 
                use_flag: e.value 
            }; 

            $.ajax({
                type: "POST",
                url: "./action/api_control_action.php",
                data: form_data,
                dataType: 'json',
                success: function(obj) {
                    if (obj.status == 1) {
                        alert('변경되었습니다.');
                        window.location.reload();
                    } else {
                        alert(obj.msg);
                        window.location.reload();
                    }
                },
                error: function(request, status, error) {
                    alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                    window.location.reload();
                }
            });
        }
        
    }
</script>