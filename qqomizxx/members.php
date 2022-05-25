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

        $query = "SELECT COUNT(*) AS cnt FROM shop_admin_member";
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
<?php include('join.php');?>
<div class="modify_refresh">
<?php include('modify.php');?>
</div>

<body oncontextmenu='return false'>
    <section class="sub_bo_Wrap">
        <div class="subTopBox">
            
            <div class="subT_SideBox">
                <div class="admin_btn">
                    <p class="btn btn-light"><?=$_SESSION['AdminID']." 님"?></p>
                    <button type="button" class="btn btn-danger" onclick="location.replace('./action/logout.php');">로그아웃</button>
                </div>
                <div class="subtName">
                    <h1>계정 관리</h1>
                    <h2>관리자 계정을 관리할 수 있습니다.</h2>
                </div>
            </div>
        </div>

        
        <div id="process_submit" class="members">
            <div class="process_sideBox">
                <div class="top_phoneBox">
                    <p class="cell text-center">
                    <?php if($_SESSION['AdminAuthority'] == 1) { ?>
                    <button class="btn manageBtn" type="button">계정 생성</button>
                    <?php } ?>
                    </p>
                </div>
                <div class="oi_inputArea">
                    <div class="recentTableBox">
                        <table>
                            <tr class="ret_tr">
                                <td class="re_top">아이디</td>
                                <td class="re_top">이름</td>
                                <td class="re_top">생성날짜</td>
                                <td class="re_top">마지막 접속날짜</td>
                            <?php if($_SESSION['AdminAuthority'] == 1) { ?>
                                <td class="re_top">비밀번호 수정</td>
                                <td class="re_top">삭제</td>
                            <?php } ?>
                            </tr>

                        <?php
                            $query = "SELECT seq_no, id, name, upd_date, reg_date FROM shop_admin_member ORDER BY reg_date desc";
                            $statement = $db->query($query);
                            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                                $seq_no = isset($row['seq_no']) ? $row['seq_no'] : '';
                                $id     = isset($row['id']) ? $row['id'] : '';
                                $name   = isset($row['name']) ? $row['name'] : '';
                                $upd_date = isset($row['upd_date']) ? $row['upd_date'] : '-';
                                $reg_date = isset($row['reg_date']) ? $row['reg_date'] : '-';
                        ?>
                        <tr class="rem_tr">
                            <td class="re_mid"><?=$id?></td>
                            <td class="re_mid"><?=$name?></td>
                            <td class="re_mid"><?=$reg_date?></td>
                            <td class="re_mid"><?=$upd_date?></td>
                        <?php
                            if($_SESSION['AdminAuthority'] == 1) {
                        ?>
                            <td class="re_mid">
                                <button class="tableBtn" type="button" onclick="popupModify('<?=$id?>')">비밀번호 수정</button>
                            </td>
                            <td class="re_mid">
                                <button class="tableBtn delete" type="button" onclick="deleteID('<?=$seq_no?>')">삭제</button>
                            </td>
                        <?php } 
                        } ?>
                        </tr>
                        </table>
                        <!--페이징-->
                        <div class="d-flex justify-content-center">
                            <ul class="pagination"><?=$pagination?></ul>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
    

    <script type="text/javascript" src="assets/js/basket.js"></script>
    <script>
    $(".popBtn.closed").click(function () {
        $(".pop.pop2").fadeOut(300);
        $(".popBg").fadeOut(300);
    });

    $(".members .btn.manageBtn").click(function () {
        $(".pop.pop2").fadeIn(300);
        $(".popBg").fadeIn(300);
    });

    function popupModify(modify_id) { 
        $(".modify_refresh").load("modify.php?id="+modify_id);
    };

    function deleteID(no) {
        if (confirm("정말 삭제하시겠습니까?") == true) {
            location.replace("./action/delete_action.php?seq_no="+no);
        } else {
            alert("취소하였습니다.");
            return;
        }
    }
    </script>
</body>
</html>