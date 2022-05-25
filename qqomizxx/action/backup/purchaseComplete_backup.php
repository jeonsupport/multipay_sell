<?php

    define("ALLOWED_WELLCOME", true);

    include_once "../algorithm/RSA.php";
    include_once "../algorithm/seed.php";
    include_once "../algorithm/cipher.php";
    include_once "smsutil.php";
    include_once "../db_connecter.php";

    class PurchaseComplete {

        //상수 정의
        const TERMINALNO     = '28'; // 20210720 (new 구매전용 pc방)
        const PAYTOOL        = 3;
        const PAYTOOLNAME    = '현금';
        const SHARETYPE      = 1;
        const GAMECODE       = 0;
        const GAMECODESTATE  = 0;


        //변수 정의
        protected static $db;
        protected static $sms;
        protected static $cipher;

        public function __construct() {
            self::$db = new MsSQL_Connecter();
            self::$db = self::$db->ConnectServer();
            self::$sms = new SmsUtil();
            self::$cipher = new Cipher();

        }

        public function occupancy($field, $query) {

            try {

                $statement = self::$db->prepare($query);
                $statement->execute($field);
                $row=$statement->fetch(PDO::FETCH_ASSOC);
                $rowCount = $statement->rowCount();
                $statement->closeCursor();  

                $productNo = isset($row['ProductNo']) ? $row['ProductNo'] : "";
                $priceChain = isset($row['PriceChain']) ? $row['PriceChain'] : "";
                $countChain = isset($row['CountChain']) ? $row['CountChain'] : "";
                $totalPrice = isset($row['TotalPrice']) ? $row['TotalPrice'] : "";
                $phoneNo    = isset($row['PhoneNo']) ? $row['PhoneNo'] : "";
                $terminalNo = self::TERMINALNO;


                if($rowCount == 0) {
                    throw new Exception("등록된 정보가 없습니다.");
                }

                $query = "
                        DECLARE @return_value INT,
                        @po_intGChargeNo BIGINT,
                        @po_strErrMsg VARCHAR(256),
                        @po_intRetVal INT,
                        @po_strDBErrMsg VARCHAR(256),
                        @po_intDBRetVal INT

                        EXEC @return_value = [dbo].[UP_CHAIN_TERMINAL_SHARE_TX_INS]
                        @pi_strTerminalNo = '$terminalNo',
                        @pi_strProductNo = '$productNo',
                        @pi_strCardAmts = '$priceChain',
                        @pi_strReqCnts = '$countChain',
                        @po_intGChargeNo = @po_intGChargeNo OUTPUT,
                        @po_strErrMsg = @po_strErrMsg OUTPUT,
                        @po_intRetVal = @po_intRetVal OUTPUT,
                        @po_strDBErrMsg = @po_strDBErrMsg OUTPUT,
                        @po_intDBRetVal = @po_intDBRetVal OUTPUT

                        SELECT @po_intGChargeNo as 'po_intGChargeNo',
                        @po_strErrMsg as 'po_strErrMsg',
                        @po_intRetVal as 'po_intRetVal',
                        @po_strDBErrMsg as 'po_strDBErrMsg',
                        @po_intDBRetVal as 'po_intDBRetVal'
                ";
                
                $query = str_replace(array("\r\n","\r","\n"),'',$query);
                
                $gchargeNo = self::occupancyData($query); 
                
                $result_array = array(
                    'gchargeNo' => $gchargeNo,
                    'phoneNo' => $phoneNo
                );

                return $result_array;

            } catch(PDOException $e) {
                throw new Exception("Database query error : occupancy");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function occupancyData($query) {
            try {

                $statement = self::$db->prepare($query);
                $statement->execute();
                $row = $statement->fetch(PDO::FETCH_ASSOC);

                $po_intGChargeNo = $row['po_intGChargeNo'];
                $po_strErrMsg    = $row['po_strErrMsg'];
                $po_intRetVal    = $row['po_intRetVal'];
                $po_strDBErrMsg  = $row['po_strDBErrMsg'];
                $po_intDBRetVal  = $row['po_intDBRetVal']; 

                if($po_intRetVal != 0) {
                    throw new Exception($po_strErrMsg.'('.$po_intRetVal.')');
                }

                return $po_intGChargeNo;

            } catch(PDOException $e) {
                throw new Exception("Database query error : occupancyData");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function purchase($gchargeNo, $totalPrice) {
            try {

                $pi_strTerminalNo    = self::TERMINALNO;
                $pi_intGChargeNo     = $gchargeNo;
                $pi_intChargedAmt    = $totalPrice;
                $pi_intPayTool       = self::PAYTOOL;
                $pi_strPayToolName   = self::PAYTOOLNAME;
                $pi_strTID           = $gchargeNo;
                $pi_strCID           = $gchargeNo;
                $pi_strTDate         = $gchargeNo;
                $pi_intShareType     = self::SHARETYPE;
                $pi_intGameCode      = self::GAMECODE;
                $pi_intGameCodeState = self::GAMECODESTATE;


                $query = 
                        "
                            DECLARE @return_value INT,
                            @po_intCashAmt MONEY,
                            @po_intTINCashAmt MONEY,
                            @po_intTOUTCashAmt MONEY,
                            @po_intCashNo BIGINT,
                            @po_intGChargeNo BIGINT,
                            @po_intChargedAmt MONEY,
                            @po_strChargeNos VARCHAR(1024),
                            @po_strSrialCodes VARCHAR(1024),
                            @po_strIndexNos VARCHAR(1024),
                            @po_strFaceAmts VARCHAR(256),
                            @po_strErrMsg VARCHAR(256),
                            @po_intRetVal INT,
                            @po_strDBErrMsg VARCHAR(256),
                            @po_intDBRetVal INT

                            EXEC @return_value = [dbo].[UP_TM_CHAIN_TERMINAL_SHARECONFIRM_TX_INS_NEW]
                            @pi_strTerminalNo = :TerminalNo,
                            @pi_intGChargeNo = :GChargeNo,
                            @pi_intChargedAmt = :ChargedAmt,
                            @pi_intPayTool = :PayTool,
                            @pi_strPayToolName = :PayToolName,
                            @pi_strTID = :TID,
                            @pi_strCID = :CID,
                            @pi_strTDate = :TDate,
                            @pi_intShareType = :ShareType,
                            @pi_intGameCode = :GameCode,
                            @pi_intGameCodeState = :GameCodeState,
                            @po_intCashAmt = @po_intCashAmt OUTPUT,
                            @po_intTINCashAmt = @po_intTINCashAmt OUTPUT,
                            @po_intTOUTCashAmt = @po_intTOUTCashAmt OUTPUT,
                            @po_intCashNo = @po_intCashNo OUTPUT,
                            @po_intGChargeNo = @po_intGChargeNo OUTPUT,
                            @po_intChargedAmt = @po_intChargedAmt OUTPUT,
                            @po_strChargeNos = @po_strChargeNos OUTPUT,
                            @po_strSrialCodes = @po_strSrialCodes OUTPUT,
                            @po_strIndexNos = @po_strIndexNos OUTPUT,
                            @po_strFaceAmts = @po_strFaceAmts OUTPUT,
                            @po_strErrMsg = @po_strErrMsg OUTPUT,
                            @po_intRetVal = @po_intRetVal OUTPUT,
                            @po_strDBErrMsg = @po_strDBErrMsg OUTPUT,
                            @po_intDBRetVal = @po_intDBRetVal OUTPUT

                            SELECT @po_intGChargeNo as 'po_intGChargeNo',
                            @po_strErrMsg as 'po_strErrMsg',
                            @po_intRetVal as 'po_intRetVal',
                            @po_strDBErrMsg as 'po_strDBErrMsg',
                            @po_intDBRetVal as 'po_intDBRetVal'
                        ";

                $query = str_replace(array("\r\n","\r","\n"),'',$query);
                $field = array(
                    ':TerminalNo'    => $pi_strTerminalNo,
                    ':GChargeNo'     => $pi_intGChargeNo,
                    ':ChargedAmt'    => $pi_intChargedAmt,
                    ':PayTool'       => $pi_intPayTool,
                    ':PayToolName'   => $pi_strPayToolName,
                    ':TID'           => $pi_strTID,
                    ':CID'           => $pi_strCID,
                    ':TDate'         => $pi_strTDate,
                    ':ShareType'     => $pi_intShareType,
                    ':GameCode'      => $pi_intGameCode,
                    ':GameCodeState' => $pi_intGameCodeState
                );

                return self::purchaseData($field, $query);


            } catch(PDOException $e) {
                throw new Exception("Database query error : purchase");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function purchaseData($field, $query) {
            try {

                $statement = self::$db->prepare($query);
                $statement->execute($field);
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                $statement->closeCursor();

                $po_intGChargeNo = $row['po_intGChargeNo'];
                $po_strErrMsg    = $row['po_strErrMsg'];
                $po_intRetVal    = $row['po_intRetVal'];
                $po_strDBErrMsg  = $row['po_strDBErrMsg'];
                $po_intDBRetVal  = $row['po_intDBRetVal']; 

                if($po_intRetVal != 0) {
                    throw new Exception($po_strErrMsg.'('.$po_intRetVal.')');
                }

                $s_query = "SELECT SerialCode, Price FROM TCHAINPURCHASEMST WHERE GChargeNo = $po_intGChargeNo";
                
                return self::tChainPurchaseMst($s_query, $po_intGChargeNo);

            } catch(PDOException $e) {
                throw new Exception("Database query error : purchaseData");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function productInfo($gchargeNo) {
            try {

                // $query = "
                //             SELECT A.ProductNo, B.ProductName, A.Price, COUNT(*) AS Cnt
                //             FROM TCHAINPURCHASEMST AS A 
                //             LEFT JOIN TProductMst AS B 
                //             ON A.ProductNo = B.ProductNo
                //             WHERE A.GChargeNo = $gchargeNo
                //             GROUP BY A.ProductNo, B.ProductName, A.Price
                //         ";
                $query = "
                            SELECT ProductNo, ProductName, SUM(Hap) AS PHap, SUM(Cnt) AS CHap
                            FROM(
                            SELECT A.ProductNo as ProductNo, B.ProductName as ProductName, SUM(A.Price)AS Hap, COUNT(*) AS Cnt
                            FROM TCHAINPURCHASEMST AS A 
                            LEFT JOIN TProductMst AS B 
                            ON A.ProductNo = B.ProductNo
                            WHERE A.GChargeNo = $gchargeNo
                            GROUP BY A.ProductNo, B.ProductName, A.Price
                            ) AS t_Table
                            GROUP BY ProductNo, ProductName

                        ";
         

                $statement = self::$db->prepare($query);
                $statement->execute();
                $rowCount = $statement->rowCount();
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                $productName = isset($row['ProductName']) ? $row['ProductName'] : "";
                $price = isset($row['PHap']) ? $row['PHap'] : "";
                $cnt = isset($row['CHap']) ? $row['CHap'] : "";
 
                if($rowCount == 0) {
                    throw new Exception("상품권 정보 추출 에러(265)");
                }

                $result_array = array(
                    'productname' => $productName,
                    'price' => $price,
                    'cnt' => $cnt
                );

                return $result_array;

            } catch(PDOException $e) {
                throw new Exception("Database query error :productInfo");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }        


        public function tChainPurchaseMst($query, $gchargeNo) {
            try {

                $prodInfoArr = self::productInfo($gchargeNo);
                $productname = $prodInfoArr['productname'];
                $cnt = $prodInfoArr['cnt'];
                $price = $prodInfoArr['price'];
                

                $strText = "[] \r\n";
                $strText .= $productname." / 총".number_format($price)."원, ".$cnt."장";
                $statement = self::$db->prepare($query);
                $statement->execute();
                $rowCount = $statement->rowCount();


                if($rowCount == 0) {
                    throw new Exception("핀번호 추출 에러(249)");
                }

                while($row=$statement->fetch(PDO::FETCH_ASSOC)) {
                    $serialCode = isset($row['SerialCode']) ? self::$cipher->decode($row['SerialCode']) : "";
                    $price = isset($row['Price']) ? $row['Price'] : "";

                    $priceLen = strlen($price);
                    // 문구 변경
                    $strPrice = "";
                    // $productname = substr($productname, 0, 2);
                    if($priceLen==6) {
                        $strPrice = substr($price, 0, 2)."만원권";
                    } else if($priceLen==5) {
                        $strPrice = substr($price, 0, 1)."만원권";
                    } else {
                        $strPrice = substr($price, 0, 1)."천원권";
                    }
                    
                    $strText .= "\r\n".$serialCode."(".$strPrice.")";
                }

                return $strText;

            } catch(PDOException $e) {
                throw new Exception("Database query error : tChainPurchaseMst");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function upResFlag($field, $query) {
            try {
                
                $statement = self::$db->prepare($query);
                $statement->execute($field);
                $rowCount = $statement->rowCount();

                if($rowCount == 0) {
                    throw new Exception("상태 변경 실패(287)");
                }

                return;


            } catch(PDOException $e) {
                throw new Exception("Database query error : upResFlag");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function inUserDepositHis($field, $query) {
            try {

                $statement = self::$db->prepare($query);
                $statement->execute($field);
                $rowCount = $statement->rowCount();

                if($rowCount == 0) {
                    throw new Exception("입급정보 남기기 실패(307)");
                }

                return;

            } catch(PDOException $e) {
                throw new Exception("Database query error : inUserDepositHis");
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        public function parseJson($data){
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // 유니코드, 역슬래시 제거
        }

        public function saveFailLog($field, $seqNo="") {
            
            $where_cond = "";
            if($seqNo=="") {
                $where_cond = " AND ResFlag = 0 ";
            } else {
                $where_cond = " AND ResFlag = 2 AND SeqNo = $seqNo";
            }

            $query = "
                        UPDATE A_PurchaseApplyList SET ResFlag = 2, Message = :Message
                        WHERE UserName = :UserName
                        AND TotalPrice = :TotalPrice
                        AND BankName = :BankName
                        {$where_cond}
                    ";

            $statement = self::$db->prepare($query);
            $statement->execute($field);
  
            return;

        }

        public function success() {

            try {
              
                $errMsg = "";
                $userName = isset($_POST['userName']) ? strip_tags($_POST['userName']) : $errMsg .= "userName; ";
                $totalPrice = isset($_POST['totalPrice']) ? strip_tags($_POST['totalPrice']) : $errMsg .= "userName; ";
                $bankName = isset($_POST['bankName']) ? strip_tags($_POST['bankName']) : $errMsg .= "userName; ";

                // 관리자에서 sms 발송시
                $adminSMS = isset($_POST['adminSMS']) ? strip_tags($_POST['adminSMS']) : "";
                $seqNo = isset($_POST['seqNo']) ? strip_tags($_POST['seqNo']) : "";

                if($adminSMS === "true") {
                    
                    if($adminSMS=="" || $seqNo=="") {
                        throw new Exception("잘못된 호출입니다.(396)");
                    }
                    $query = "
                                SELECT * FROM A_PurchaseApplyList
                                WHERE SeqNo = :SeqNo
                                AND UserName = :UserName
                                AND TotalPrice = :TotalPrice
                                AND BankName = :BankName    
                            ";

                    $field = array(
                        ":SeqNo"      => $seqNo,
                        ":UserName"   => $userName,
                        ":TotalPrice" => $totalPrice,
                        ":BankName"   => $bankName
                    );
                    
                } else {

                    if($errMsg !== "") {
                        throw new Exception("잘못된 호출입니다.(4023)");
                    } else {
                        
                        // 입금정보 등록
                        $query = "INSERT INTO A_UserDepositHis(BankName, UserName, TotalPrice) VALUES (:BankName, :UserName, :TotalPrice)";
                        $field = array(
                            ":BankName" => $bankName,
                            ":UserName" => $userName,
                            ":TotalPrice" => $totalPrice
                        );
    
                        self::inUserDepositHis($field, $query);
                    }

                    // 등록 데이터 추출
                    $query = "
                        SELECT ProductNo, PriceChain, CountChain, TotalPrice, PhoneNo FROM A_PurchaseApplyList
                        WHERE UserName = :UserName
                        AND TotalPrice = :TotalPrice
                        AND BankName   = :BankName
                        AND ResFlag = 0
                    ";

                    $field = array(
                        ":UserName"   => $userName,
                        ":TotalPrice" => $totalPrice,
                        ":BankName"   => $bankName
                    );

                }
                

                // ------------------------------------------------------------------------------------
                // 상품권 점유
                $resArr = self::occupancy($field, $query);
                $occGChargeNo = $resArr['gchargeNo'];
                $phoneNo   = $resArr['phoneNo'];
                // ------------------------------------------------------------------------------------


                // ------------------------------------------------------------------------------------
                // 상품권 구매
                $text = self::purchase($occGChargeNo, $totalPrice); 
                // ------------------------------------------------------------------------------------


                // ------------------------------------------------------------------------------------
                // 상태값 변경
                $where_cond = "";
                if($seqNo=="") {
                    $where_cond = " AND ResFlag = 0 ";
                } else {
                    $where_cond = " AND ResFlag = 2 AND SeqNo = $seqNo";
                }
                $query = "
                            UPDATE A_PurchaseApplyList SET ResFlag = 1, GChargeNo = :GChargeNo
                            WHERE UserName = :UserName
                            AND TotalPrice = :TotalPrice
                            AND BankName = :BankName
                            {$where_cond}
                        ";
                $field = array(
                    ":GChargeNo"  => $occGChargeNo,
                    ":UserName"   => $userName,
                    ":TotalPrice" => $totalPrice,
                    ":BankName"   => $bankName
                );

                self::upResFlag($field, $query);
                // ------------------------------------------------------------------------------------

                //sms
                $sms = self::$sms->sendSmsMember($text, $phoneNo);
                $result_array = array(
                    'status' => '1',
                    'msg' => $sms
                );

                echo self::parseJson($result_array);
            
            
            } catch(ErrorException $e) {
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if($errMsg == "") {

                    
                    $field = array(
                        ":Message"    => $e->getMessage(),
                        ":UserName"   => $userName,
                        ":TotalPrice" => $totalPrice,
                        ":BankName"   => $bankName
                    );
                    
                    self::saveFailLog($field, $seqNo);
                }

            } catch(Exception $e) {
                echo json_encode(array('status' => 0, 'msg' => 'fail', 'data' => $e->getMessage()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if($errMsg == "") {

                            
                    $field = array(
                        ":Message"    => $e->getMessage(),
                        ":UserName"   => $userName,
                        ":TotalPrice" => $totalPrice,
                        ":BankName"   => $bankName
                    );

                    self::saveFailLog($field, $seqNo);
                }
            }
        }
    }


    $realPurchase = new PurchaseComplete();
    $realPurchase->success();
