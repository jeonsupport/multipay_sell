<?php
session_start();

include_once "../PHPExcel-1.8/Classes/PHPExcel.php";
include_once "../../db_connecter.php";
include_once "../../inc.php";


$objPHPExcel = new PHPExcel();
$conn = new MySQL_Connecter();
$db = $conn->ConnectServer();

// 엑셀 데이터를 담을 배열을 선언한다.
$allData = array();

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $_FILES['excelFile']['tmp_name']);

try {

    if($filename=="") {
      throw new Exception('업로드할 엑셀파일을 선택해 주세요.');
    }

    // 업로드한 PHP 파일을 읽어온다.
    $objPHPExcel = PHPExcel_IOFactory::load($filename);

    $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
    $sheetsCount = $objPHPExcel -> getSheetCount();

    // 시트Sheet별로 읽기
    for($sheet = 0; $sheet < $sheetsCount; $sheet++) {

          $objPHPExcel -> setActiveSheetIndex($sheet);
          $activesheet = $objPHPExcel -> getActiveSheet();
          $highestRow = $activesheet -> getHighestRow();           // 마지막 행
          $highestColumn = $activesheet -> getHighestColumn();    // 마지막 컬럼

          // 한줄읽기
          for($row = 1; $row <= $highestRow; $row++) {

            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $activesheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);

            // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
            $allData[$row] = $rowData[0];
          }
    }

    //핀코드 배열 추출
    $new_array = array();
    for($i=0; $i<=count($allData)-1; $i++) {
      $new_array[$i] = $allData[$i+1][0];
    }
    
    //api 전달
    $arrSendPinCode = array(
      'action'   => 'excel',
      'auth'     => API_AUTH_TOKEN,
      'pinCode' => $new_array
    );
    $resData = post(API_URL, $arrSendPinCode);
    $result = json_decode($resData, true);

    if($result['status'] == 0) { throw new Exception($result['msg']);}
    

    if($result['failpin'] != 'null') { // 모두 실패, 부분실패
      
      inFail($result['failpin']);
      if($result['successdata'] != 'null') {
        inSuccess($result['successdata']);
      }
      throw new Exception('실패 핀번호가 있습니다.');

      
    } else {

      inSuccess($result['successdata']);
      throw new Exception('업로드를 완료했습니다.');
    }
    

} catch(Exception $e) {
  msgback($e->getMessage());
} catch(PDOException $e) {
  msgback($e->getMessage());
}



function inFail($data) {

  try {
    if(isset($data) && !empty($data)) {

      $db = $GLOBALS['db'];
      $arr = array();
      $arr = explode(',', $data);

      // 트랜잭션 시작
      $db->beginTransaction();

      $query = "INSERT INTO shop_fail_upload_excel_list (serial_code) VALUES (:serial_code)";
      $statement = $db->prepare($query);

      foreach($arr as $fail_pin_num) {

        $statement->bindValue(':serial_code', $fail_pin_num);
        $statement->execute();

      }

      $db->commit();
      return;

    }
  } catch(PDOException $e) {
    throw new Exception($e->getMessage());
  } catch(Exception $e) {
    throw new Exception($e->getMessage());
  }
}


function inSuccess($data) {


  try {
    if(isset($data) && !empty($data)) {

      $db = $GLOBALS['db'];
      $arr = array();
      $arr = explode(',', $data);
      $id = $_SESSION['AdminID'];
      $ip = get_client_ip();

      // 트랜잭션 시작
      $db->beginTransaction();
  
      $query = "INSERT INTO shop_qr_serial_code_list (serial_code, price, product_no, admin_ip, admin_id)
                VALUES (:serial_code, :price, :product_no, :admin_ip, :admin_id)";
      $statement = $db->prepare($query);
  
      foreach($arr as $v) {
        list($productno, $price, $token) = explode('|', $v);
  
        $statement->bindValue(':serial_code', $token);
        $statement->bindValue(':price', $price);
        $statement->bindValue(':product_no', $productno);
        $statement->bindValue(':admin_ip', $ip);
        $statement->bindValue(':admin_id', $id);
        $statement->execute();
      }
  
      $db->commit();
      return;
  
    } else {
      throw new Exception("성공 핀번호가 없습니다.");
    }
  
  } catch(PDOException $e) {
    $db->rollBack();
    throw new Exception($e->getMessage());
  } catch(Exception $e) {
    $db->rollBack();
    throw new Exception($e->getMessage());
  }
  
}

