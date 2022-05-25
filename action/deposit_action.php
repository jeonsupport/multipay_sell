<?php


    class DepositAction {

        private $db;

        public function action($user_name, $total_price, $bank_name, $p_seq_no, $d_seq_no) {

            try {

                $conn = new MySQL_Connecter();
                $this->db = $conn->ConnectServer();

                if ($user_name=='' || $total_price == '' || $bank_name == '') {
                    throw new Exception("입금처리 에러");
                }
                    
                //step1. api request data 추출
                $api_array = $this->apiRequest($user_name, $total_price, $bank_name, $p_seq_no);


                //step2. 사용자 데이터 업데이트
                // 트랜잭션 시작
                $this->db->beginTransaction();

                // 구매 리스트 사용 완료 처리
                $query = 
                    "
                        UPDATE shop_user_purchase_list
                        SET upd_date = now(3), token = :token, res_flag = 2
                        WHERE user_name = :user_name
                        AND total_price = :total_price
                        AND bank_name = :bank_name
                        AND res_flag = 0
                        AND seq_no = :seq_no
                    ";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':token', $api_array['token']);
                $statement->bindValue(':user_name', $user_name);
                $statement->bindValue(':total_price', $total_price);
                $statement->bindValue(':bank_name', $bank_name);
                $statement->bindValue(':seq_no', $p_seq_no);
                $statement->execute();
                $rowCount = $statement->rowCount();

                if ($rowCount <> 1) {
                    $this->db->rollBack();
                    throw new Exception("구매 리스트 사용 완료 처리 실패");
                }

                // 잔액 업데이트
                $query = "UPDATE shop_balance SET balance = :balance";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':balance', $api_array['balance']);
                $statement->execute();
                $rowCount = $statement->rowCount();

                // 잔액 로그 업데이트
                $query = 
                    "
                        INSERT INTO shop_balance_log (buyer_phone_no, before_balance, buy_price, return_balance)
                        VALUES (:buyer_phone_no, :before_balance, :buy_price, :return_balance)
                    ";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':buyer_phone_no', $api_array['phone_no']);
                $statement->bindValue(':before_balance', $api_array['before_balance']);
                $statement->bindValue(':buy_price', $total_price);
                $statement->bindValue(':return_balance', $api_array['balance']);
                $statement->execute();
                $rowCount = $statement->rowCount();

                if ($rowCount <> 1) {
                    $this->db->rollBack();
                    throw new Exception("잔액 로그 업데이트 실패");
                }

                
                // 성공시 입금 상태 업데이트 0 -> 1
                $query = "UPDATE shop_user_deposit_list SET use_state = 1 WHERE seq_no = :seq_no";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':seq_no', $d_seq_no);
                $statement->execute();
                $rowCount = $statement->rowCount();

                if ($rowCount <> 1) {
                    $this->db->rollBack();
                    throw new Exception("입금 상태 업데이트 실패");
                }

                // 성공 시 커밋
                $this->db->commit();

                $result_array = array (
                    'status' => 1,
                    'msg'    => 'ok'
                );
                

                return $result_array;

        
            } catch(PDOException $e) {
    
                return array('status' => 0, 'msg' =>  $e->getMessage());
                
            } catch(Exception $e) {

                return array('status' => 0, 'msg' =>  $e->getMessage());
            }
        }

        public function apiRequest($user_name, $total_price, $bank_name, $p_seq_no) {

            $query = 
                "
                    SELECT product_no, total_price, total_count, phone_no
                    , (SELECT balance FROM shop_balance) AS balance
                    FROM shop_user_purchase_list 
                    WHERE user_name = :user_name
                    AND real_price = :real_price
                    AND bank_name = :bank_name
                    AND res_flag = 0
                    AND seq_no = :seq_no
                ";
                
            $statement = $this->db->prepare($query);
            $statement->bindValue(':user_name', $user_name);
            $statement->bindValue(':real_price', intval($total_price));
            $statement->bindValue(':bank_name', $bank_name);
            $statement->bindValue(':seq_no', $p_seq_no);
            $statement->execute();
            $rowCount = $statement->rowCount();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            
            $product_no     = isset($row['product_no'])  ? $row['product_no']  : '';
            $total_price    = isset($row['total_price']) ? $row['total_price'] : '';
            $total_count    = isset($row['total_count']) ? $row['total_count'] : '';
            $before_balance = isset($row['balance'])     ? $row['balance']     : '';
            $phone_no       = isset($row['phone_no'])    ? $row['phone_no']    : '';
            if ($before_balance < $total_price) { throw new Exception("잔액이 부족합니다."); }
            if ($rowCount == 0) { throw new Exception("구매 정보가 없습니다."); }

            // api request
            $arr_data = array (
                'action'          => 'qr_update'
                , 'auth'          => API_AUTH_TOKEN
                , 'product_no'    => intval($product_no)
                , 'total_price'   => intval($total_price)
                , 'total_count'   => intval($total_count)
            );
            $json_result = post(API_URL, $arr_data);
            $arr_result = json_decode($json_result, true);
            
            if ($arr_result['status'] == 0) {
                err_log_insert($arr_result['msg']);
            }
            $status  = isset($arr_result['status'])  ? $arr_result['status']  : '';
            $msg     = isset($arr_result['msg'])     ? $arr_result['msg']     : '';
            $balance = isset($arr_result['balance']) ? $arr_result['balance'] : '';
            $token   = isset($arr_result['token'])   ? $arr_result['token']   : '';

            $result_array = array (
                'before_balance' => $before_balance
                , 'phone_no'     => $phone_no
                , 'balance'      => $balance
                , 'token'        => $token
            );

            return $result_array;

        }

    

 
    }
