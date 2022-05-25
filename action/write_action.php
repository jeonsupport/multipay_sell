<?php
    session_start();


    // DB connect
    include_once "../db_connecter.php";
    include_once "../inc.php";;

    $conn = new MySQL_Connecter();
    $db = $conn->ConnectServer();

    $page = isset($_POST['page']) ? $_POST['page'] : "";
    $sch_field = isset($_POST['sch_field']) ? $_POST['sch_field'] : "";
    $sch_keyword = isset($_POST['sch_keyword']) ? $_POST['sch_keyword'] : "";
    $seq_no = isset($_POST['seq_no']) ? $_POST['seq_no'] : "";
    $kind = isset($_POST['kind']) ? $_POST['kind'] : "";
    $title = isset($_POST['title']) ? strip_tags($_POST['title']) : "";
    $name = isset($_POST['name']) ? strip_tags($_POST['name']) : "";
    $contents = isset($_POST['contents']) ? strip_tags($_POST['contents']) : "";
    $pw = isset($_POST['pw']) ? password_hash($_POST['pw'], PASSWORD_DEFAULT) : "";
    $capcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';


    try {

        // 캡챠 검증
        if ($capcha !== $_SESSION['str']) {
            throw new Exception("이미지 글자가 일치하지 않습니다.");
        }


        if(isset($kind) && !empty($kind)) {

            if($kind=="user" && $title && $name && $contents && $pw) { // 사용자 글쓰기
                $query = "INSERT INTO shop_notice_board(title, grp_no, grp_ord, depth, writer, contents, pwd) VALUES (:title, :grp_no, :grp_ord, :depth, :writer, :contents, :pwd);";
                $statement = $db->prepare($query);
                $statement->bindValue(':title', $title);
                $statement->bindValue(':grp_no', 0);
                $statement->bindValue(':grp_ord', 1);
                $statement->bindValue(':depth', 0);
                $statement->bindValue(':writer', $name);
                $statement->bindValue(':contents', $contents);
                $statement->bindValue(':pwd', $pw);
                $statement->execute();
                $rowCount = $statement->rowCount();

                if ($rowCount <> 1) {
                    throw new Exception("글쓰기 실패");
                }

                $last_insert_id = $db->lastInsertId();
                $seq_no = $last_insert_id;
                $page = 1;

                $_SESSION['seq_no'] = $seq_no;

            } else {
                throw new Exception("database query error");
            }

            $url = "../qna_detail.php?page=$page&seq_no=$seq_no&sch_field=$sch_field&sch_keyword=$sch_keyword";

            movepage($url, "처리완료");

        } else {
            throw new Exception("database query error(1)");
        }

    } catch(PDOException $e) {
        msgback("database query error");
    } catch(Exception $e) {
        msgback($e->getMessage());
    }


