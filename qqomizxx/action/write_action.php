<?php

    // DB connect
    include_once "../../db_connecter.php";
    include_once "../../inc.php";

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $r_seq_no = isset($_POST['r_seq_no']) ? $_POST['r_seq_no'] : "";
    $page = isset($_POST['page']) ? $_POST['page'] : "";
    $sch_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : "";
    $sch_keyword = isset($_POST['sch_keyword']) ? $_POST['sch_keyword'] : "";
    $seq_no = isset($_POST['seq_no']) ? $_POST['seq_no'] : "";
    $kind = isset($_POST['kind']) ? $_POST['kind'] : "";
    $title = isset($_POST['title']) ? $_POST['title'] : "";
    $name = isset($_POST['name']) ? $_POST['name'] : "";
    $contents = isset($_POST['contents']) ? $_POST['contents'] : "";

    //관리자 게시물, 답글 삭제 요청시
    $admin_param = isset($_GET['kind']) ? $_GET['kind'] : "";
    if($admin_param) {
        $kind = isset($_GET['kind']) ? $_GET['kind'] : ""; 
        $r_seq_no = isset($_GET['r_seq_no']) ? $_GET['r_seq_no'] : "";
        $seq_no = isset($_GET['seq_no']) ? $_GET['seq_no'] : "";
        $page = isset($_GET['page']) ? $_GET['page'] : "";
        $sch_field = isset($_GET['sch_field']) ? $_GET['sch_field'] : "";
        $sch_keyword = isset($_GET['sch_keyword']) ? $_GET['sch_keyword'] : "";
    }
    
    try {

        // 이전 페이지 체크
        if (!check_referer()) {
            throw new Exception("잘못된 접근입니다.");
        }

        if(isset($kind) && !empty($kind)) {

            if($kind=="in_admin" && $page && $seq_no && $contents) { // 관리자 답글 쓰기

                $query = "
                            INSERT INTO shop_notice_board(grp_no, writer, contents) VALUES ($seq_no, '관리자', '$contents');
                            UPDATE shop_notice_board SET grp_ord = 2 WHERE seq_no = $seq_no;
                        ";

                updData($query);

            } else if($kind=="up_admin" && $page && $seq_no && $contents && $r_seq_no) { // 관리자 답글 수정

                $query = "
                            UPDATE shop_notice_board SET contents = '$contents' WHERE seq_no = $r_seq_no;
                            UPDATE shop_notice_board SET grp_ord = 2 WHERE seq_no = $seq_no;
                        ";

                updData($query);

            } else if($kind=="r_del") { // 관리자 답글 삭제

                $query = "
                            UPDATE shop_notice_board SET use_flag = 1 WHERE seq_no = $r_seq_no;
                            UPDATE shop_notice_board SET grp_ord = 1 WHERE seq_no = $seq_no;
                        ";
                updData($query);
            
            } else if($kind=="p_del") { // 관리자 게시물 삭제

                $query = "UPDATE shop_notice_board SET use_flag = 1 WHERE seq_no = $seq_no";
                updData($query);

                movepage("../qna.php?page=$page&sch_field=$sch_field&sch_keyword=$sch_keyword", "처리완료");
            
            } else {
                throw new Exception("database query error(0)");
            }

            $url = "../admin_qna_detail.php?page=$page&seq_no=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword";

            movepage($url, "처리완료");

        } else {
            throw new Exception("database query error(1)");
        }

    } catch(PDOException $e) {
        msgback("database query error(2)");
    } catch(Exception $e) {
        msgback($e->getMessage());
    }

    function updData($query, $kind="") {
        try {
            
          $db = $GLOBALS['db'];
          $statement = $db->prepare($query);
          $statement->execute();
          $rowCount = $statement->rowCount();
    
          
          if($rowCount==0) {
              throw new Exception("database query error(3_1)");
          }

          return;
    
        } catch(PDOException $e) {
            throw new Exception("database query error(4)");
            //echo $query;
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
