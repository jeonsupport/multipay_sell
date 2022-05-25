let product = "";
let timerID;
let global_type;
let click_type = 0; // 0: first, 1:second
let cond_no = 0;
let doubleSubmitFlag = false; // ajax에서의 더블써밋 방지


const QR_BUY_MAX_COUNT = 50;
const SMS_BUY_MAX_COUNT = 20;


//장바구니
let basket = {
    totalCount: 0,
    totalPrice: 0,
    totalAmountStr: "",
    totalRate: 0,
    t_amount: QR_BUY_MAX_COUNT, // new 구매 수량 제한

    buy_type: function(kind) { //1:qr, 2:sms

        if(kind==1) {
            this.t_amount = QR_BUY_MAX_COUNT;
        } else {
            this.t_amount = SMS_BUY_MAX_COUNT;
        }

        //초기화
        var cnt = 0;
        document.querySelectorAll(".p_num").forEach(function (item) {
            cnt++;
            var item = document.getElementById("p_num"+cnt);    
            item.value = 0;
            item.setAttribute('value', item.value);

            var price = item.parentElement.parentElement.parentElement.firstElementChild.firstElementChild.getAttribute('value');
            item.parentElement.parentElement.parentElement.firstElementChild.nextElementSibling.firstElementChild.textContent = (item.value * price).formatNumber() + "원";
            this.reCalc();
            this.updateUI();
        }, this);

    },

    //재계산
    reCalc: function (pos) {
        this.totalCount = 0;
        this.totalPrice = 0;
        this.totalAmountStr = "";
        this.totalRate = document.getElementById('rate').value;
        document.querySelectorAll(".p_num").forEach(function (item) {
            var count = parseInt(item.getAttribute('value'));
            this.totalCount += count;
            var price = item.parentElement.parentElement.parentElement.firstElementChild.firstElementChild.getAttribute('value');
            this.totalPrice += count * price;
            this.totalAmountStr += count + "," + price + "|";
        }, this);

        if(this.totalCount > this.t_amount) {

            alert("구매 수량이 맞지 않습니다.(1~"+this.t_amount+"장)");

            try {
                var item = document.getElementById("p_num"+pos);    
                item.value = 0;  //            item.value = item.value - 1; 
                item.setAttribute('value', item.value);

                var price = item.parentElement.parentElement.parentElement.firstElementChild.firstElementChild.getAttribute('value');
                item.parentElement.parentElement.parentElement.firstElementChild.nextElementSibling.firstElementChild.textContent = (item.value * price).formatNumber() + "원";

                this.reCalc();
                this.updateUI();
            } catch(err) {
                // console.log(err);
            }
            
        }
      

    },
    //화면 업데이트
    updateUI: function () {
    
        document.querySelector('#sum_p_num').textContent = this.totalCount.formatNumber();
        // document.querySelector('#sum_p_price1').textContent = this.totalPrice.formatNumber();
        document.querySelector('#sum_p_price2').textContent = this.totalPrice.formatNumber();
        document.querySelector('#sum_p_price3').textContent = (this.totalPrice - this.totalPrice * (this.totalRate * 0.01)).formatNumber();
    },
    //개별 수량 변경
    changePNum: function (pos) {

    
        var item = document.getElementById("p_num"+pos);    
        var p_num = parseInt(item.value);
        var newval = event.target.classList.contains('plus') ? p_num + 1 : event.target.classList.contains('minus') ? p_num - 1 : event.target.value;


        if (parseInt(newval) < 0 || parseInt(newval) > this.t_amount || isNaN(p_num)) {

            if(newval != '') {

                if(isNaN(p_num)) {
                    alert('올바른 값을 입력하세요.');
                } else {
                    alert("구매 수량이 맞지 않습니다.(1~"+this.t_amount+"장)");
                }

            }
            
            var item = document.getElementById("p_num"+pos);    
            if(newval != '') {
                item.value = 0;
            }
            
            item.setAttribute('value', 0);
            var price = item.parentElement.parentElement.parentElement.firstElementChild.firstElementChild.getAttribute('value');
            item.parentElement.parentElement.parentElement.firstElementChild.nextElementSibling.firstElementChild.textContent = (0 * price).formatNumber() + "원";

            this.reCalc();
            this.updateUI();

            return false;
        }

  
        item.setAttribute('value', newval);
        item.value = newval;


        var price = item.parentElement.parentElement.parentElement.firstElementChild.firstElementChild.getAttribute('value');
        item.parentElement.parentElement.parentElement.firstElementChild.nextElementSibling.firstElementChild.textContent = (newval * price).formatNumber() + "원";


        //전송 처리 결과가 성공이면    
        this.reCalc(pos);
        this.updateUI();


    },



    //발권하기 버튼 누르면 무통장입금 결제 내역창 
    event_btnBuy: function () {

        // 잔액체크(텔레그램)
        //telegram();

        
        let productNo = product;
        let payment = $('input:radio[name=payment_type]').is(':checked');
        let amount = this.totalAmountStr.slice(0, -1);
        let phoneNo = document.getElementById("use_tel").value;
        let userName = document.getElementById("use_account_name").value;

        let bankName = $('select[name=bankName]').val();
        let pop_bankName = document.getElementById("bankName");
        let clipInput = document.getElementById("clipInput");
        let pop_totPrice = document.getElementById("pop_totPrice");
        let pop_userName = document.getElementById("pop_userName");


        //회원 등록 정규식
        let patternPhone = /01[016789]-[^0][0-9]{2,3}-[0-9]{3,4}/; // 핸드폰번호 정규식
        let patternName = /([^가-힣\x20a-zA-Z])/i; //한글과 영어만
        let buy_type = $('input:radio[name=buy_type]:checked').val(); // qr or sms

        global_type = buy_type;
   
        if (payment == false) {
            alert("결제수단을 선택해주세요.");
            return;
        } else if (this.totalCount == 0) {
            alert("권종을 선택해주세요.");
            return;
        } else if (bankName == '') {
            alert("입금은행을 선택하세요.");
            return;
        } else if (!patternPhone.test(phoneNo) || phoneNo == "") {
            alert("핸드폰 번호를 확인해주세요.");
            return;
        } else if (patternName.test(userName) || userName == "" || userName.length > 15) {
            alert("입금자명을 정확하게 입력하세요.");
            return;
        } 

        if(this.totalCount > QR_BUY_MAX_COUNT) {
            alert("최대구매수량은 "+QR_BUY_MAX_COUNT+"장 입니다.");
            return;
        }


        //본인인증
        let m_timeout = false;
        // let frmPop= document.frmPopup;
        // let authData = new Object();
        // authData.phoneNo = phoneNo;
        // authData.userAuth = true;
        // $.ajax({
        //     url: './action/auth.php',
        //     type: 'post',
        //     async: false,
        //     data: {
        //         "authData": authData
        //     },
        //     dataType: 'json',
        //     success: function (data) {
        //         if (data.status == 0) {
        //             location.reload();
        //             alert(data.data);
        //         } else {
        //             m_timeout = data.timeout;
        //             if(m_timeout==true) {

        //                 if(!confirm("구매를 위해 본인인증이 필요합니다. \n본인인증 하시겠습니까?")) {
        //                     return;
        //                 }
        //                 window.open('','danalView',"width=600,height=800,left=600");
        //                 frmPop.action = './danal/Ready.php';
        //                 frmPop.target = 'danalView'; //window,open()의 두번째 인수와 같아야 하며 필수다.  
        //                 frmPop.user_no.value = data.seqno;
        //                 frmPop.submit();   
        //             }
        //         }
        //    },
        //     error: function (err) {
        //         console.log("just only error!! : " + err);
        //    }
        // });

        if(doubleSubmitFlag) { // 더블써밋 플래그가 true가 되면 '처리중' 실행
            alert('처리중입니다...');
            return false;
        }

        //구매 상품정보 보여주기, 중복체크
        if(m_timeout==false) {
            let sendData = new Object();
            sendData.productNo = productNo;
            sendData.payment = payment;
            sendData.amount = amount;
            sendData.phoneNo = phoneNo;
            sendData.userName = userName;
            sendData.totalCount = this.totalCount;
            sendData.totalPrice = this.totalPrice;
            sendData.buy_type = buy_type;
            sendData.bankName = bankName;


            if ($('.productList').css('display') == 'block') {

                doubleSubmitFlag = true; // ajax 실행시 플래그 값 true

                $.ajax({
                    url: './action/purchase.php',
                    type: 'post',
                    data: {
                        "sendData": sendData
                    },
                    dataType: 'json',
                    async:false,
                    success: function (data) {
                        if (data.status == 0) {
                            $('.productList,#basket').show();
                            $('.productPop').hide();
                            alert(data.data);
                        } else {
                            $('.productList,#basket').hide();
                            $('.productPop').show();
                            //220331 app체크팝업 추가
                            var cookie_val = getCookie('pop_cookie');
                            if (cookie_val != 'ok') {
                                $('.check_app,.bg').show();
                                $('main').addClass('blur');
                            }
                            pop_totPrice.innerHTML = numberWithCommas(data.totalprice)+"원";
                            pop_userName.innerHTML = data.username;
                            pop_bankName.innerHTML = data.bankname;
                            clipInput.value = data.account;
                            if(buy_type=="qr") {
                                // let strDom = '<div class="qrimgBox"><p>계좌이체가 완료되면 실시간으로 QR코드 이미지가 노출됩니다.</p><p class="blinker">*주의*<br>입금 확인이 될 때까지 본 화면을 닫지 마세요.<br>입금 전 본 화면을 닫으면 구매신청이 자동 취소됩니다.</p><b class="pop_pass">실시간 입금현황 비밀번호<span>'+String(data.pwd)+'</span></b></div>';
                                let strDom = '<div class="qrimgBox"><p>계좌이체가 완료되면 실시간으로 QR코드 이미지가 노출됩니다.</p><p class="blinker">*주의*<br>입금 확인이 될 때까지 본 화면을 닫지 마세요.<br>입금 전 본 화면을 닫으면 구매신청이 자동 취소됩니다.</p><br><br></div>';
                                let pop_qrimg = $("#pop_qrimg");
                                pop_qrimg.before(strDom);

                                cond_no = data.seq_no;
                                updateQrData(data.seq_no, String(data.pwd)); //2022-01-06 수정
                                //alert("실시간 입금 현황 비밀번호를 문자로 발송하였습니다.");
                            }
                        }
                    },
                    error: function (err) {
                        console.log("just only error!! : " + err);
                    }
                });

                doubleSubmitFlag = false;


            } else {
                location.reload();

            }
        }
        //--------------------------------------------------------------------------------------------------

        //        alert("발권을 위해 입금 내역과 동일한 입금자명으로 입금 바랍니다.\n * 불일치할 경우 고객센터로 문의 바랍니다.");
        // if ($('.productList').css('display') == 'block') {
        //     $('.productList').hide();
        //     $('.productPop').show();
        // } else {
        //     $('.productList').show();
        //     $('.productPop').hide();
        // }


    }
};


function telegram() {

    $.ajax({
        url: "./action/telegram_check.php",
        type:"post",
        dataType: 'json',
        async:false,
        // cache : false,
        success: function(data){ 
            if(data.status == 1) {
                console.log(data.msg);
            } else {
                console.log(data.msg);
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
        }
      });
}



function createImage(data, pwd, token) { 

    // $('.qrimgBox').html('<img src="' + data + '"><button onclick="action_app(\'' + token + '\');">APP에서 상품권 내려받기</button><p>멀티페이 APP을 실행해 [QR스캔]을 눌러 본 화면의 QR코드를 스캔하세요.</p><b class="pop_pass">실시간 입금현황 비밀번호<span>'+String(pwd)+'</span></b>');
    $('.qrimgBox').html('<img src="' + data + '"><button onclick="action_app(\'' + token + '\');">APP에서 상품권 내려받기</button><p>멀티페이 APP을 실행해 [QR스캔]을 눌러 본 화면의 QR코드를 스캔하세요.</p><br><br>');

}



function scanConfirm(token) {


    $.ajax({
        url: "./action/scan_confirm.php",
        type:"post",
        data:{"token":token},
        dataType: 'json',
        async:false,
        // cache : false,
        success: function(data){ 
            if(data.status == 1) {
                clearTimeout(timerID); // 타이머 중지
                timerID = '';//타이머 리셋
                if(timerID=='') {
                    purchaseComplete(token); // 구매완료처리
                    alert('QR핀코드 스캔이 완료되었습니다.');
                    location.reload();
                }
            } else {
                timerID = setTimeout("scanConfirm('"+token+"')", 3000); // 3초 단위로 갱신 처리
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
            timerID = setTimeout("scanConfirm('"+token+"')", 3000); // 3초 단위로 갱신 처리
        }
      });
}


function purchaseComplete(token) {
    $.ajax({
        url: "./action/purchase_complete.php",
        type:"post",
        data:{"token":token},
        dataType: 'json',
        // cache : false,
        success: function(data){ 
            if(data.status == 1) {
                return;
            } else {
                // alert("구매완료 처리 실패");
                return;
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
        }
      });
}


function updateQrData(seq_no, pwd) { // 2022-01-06 수정

    $.ajax({
        url: "./action/real_time_account_transfer.php",
        type:"post",
        data:{"sendData":seq_no},
        dataType: 'json',
        // cache : false,
        success: function(data){ 
            if(data.status == 1) {
                alert("입금이 확인되었습니다.");

                click_type = 1; 
                $('#buy_cancel_btn').html("확인");
                createImage(data.img_url, String(pwd), data.token);
                clearTimeout(timerID); // 타이머 중지
                timerID = '';//타이머 리셋

                scanConfirm(data.token);

            } else if (data.data == 'deposit_error') {
                alert("입금 내역 조회 오류");
                location.reload();
            } else {
                timerID = setTimeout("updateQrData("+seq_no+","+pwd+")", 5000); // 1초 단위로 갱신 처리 // 2022-01-06 수정
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
            timerID = setTimeout("updateQrData("+seq_no+","+pwd+")", 5000); // 1초 단위로 갱신 처리 // 2022-01-06 수정
        }
      });
      
}


//숫자 세자리 콤마찍기
Number.prototype.formatNumber = function () {
    if (this == 0) return 0;
    let regex = /(^[+-]?\d+)(\d{3})/;
    let nstr = (this + '');
    while (regex.test(nstr)) nstr = nstr.replace(regex, '$1' + ',' + '$2');
    return nstr;
};

//장바구니 전체 비우기
function delAllItem() {
    location.reload();
};

$("#basketEmpty").show();
$("#sideMenu").hide();

//로고 누르면 새로고침
$('#logo').click(function () {
    location.reload();
});


//창넓이 사이즈
var windowWidth = $(window).width();
if (windowWidth < 860) {
    //창 넓이가 860 미만일 경우
    
    $('.notSelected').click(function () {
        $('.notSelected').hide();
    });
    
    $('.btnBuy').click(function () {
        $('#sideMenu').hide();
    });

} else {
    //창 넓이가 860 이상일 경우
};

function baseName(str)
{
   var base = new String(str).substring(str.lastIndexOf('/') + 1); 
    if(base.lastIndexOf(".") != -1)       
        base = base.substring(0, base.lastIndexOf("."));
   return base;
}


// 초기화면 설정
if (baseName(window.location.pathname) === 'shop') {
    $(document).ready(function(){

        i = '문화상품권';
        h = 1008;
        product = h;

        let sendData = new Object();
        sendData.productNo = product;

        $.ajax({
            url: './action/side_menu.php',
            type: 'POST',
            data: {
                "sendData": sendData
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    location.replace('index.php');
                    alert(data.data);
                } else {
                    sideMenu.innerHTML = data.sideMenu;
                    $('.prdName span').text(i);
                }
            },
            error: function (err) {
                console.log("just only error!! : " + err);
            }
        });

        $("#sideMenu").show();
        $("#basketEmpty").hide();
});

}



//상품권 클릭하면 해당 상품권 페이지 오픈
var sideMenu = document.getElementById("sideMenu");
$('.prd>li').click(function () {
    $('.prd>li').removeClass('active');
    if (!$(this).hasClass('active')) {
        $(this).addClass('active');

        //상품권 이름 변경
        i = $(this).find(".thumb img").attr('alt');

        h = $(this).find(".thumb img").attr('class');
        product = h;

        //AJAX
        let sendData = new Object();
        sendData.productNo = product;

        $.ajax({
            url: './action/side_menu.php',
            type: 'POST',
            data: {
                "sendData": sendData
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    location.reload();
                    alert(data.data);
                } else {
                    sideMenu.innerHTML = data.sideMenu;
                    $('.prdName span').text(i);
                }
            },
            error: function (err) {
                console.log("just only error!! : " + err);
            }
        });

        $("#sideMenu").show();
        $("#basketEmpty").hide();


    } else {
        $(this).removeClass('active');
    }
});


//3자리수 콤마
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


//계좌번호 복사 클립
function copy_to_clip() {
    var copyText = document.getElementById('clipInput');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("Copy");
};

//휴대폰번호 하이픈넣기 및 숫자만 입력
$(document).on("keyup", ".phoneNumber", function () {
    $(this).val($(this).val().replace(/[^0-9]/g, "").replace(/(^02|^0505|^1[0-9]{3}|^0[0-9]{2})([0-9]+)?([0-9]{4})$/, "$1-$2-$3").replace("--", "-"));
});

//특수문자 숫자 입력 불가
$(document).on("keyup", ".use_account_name", function () {
    $(this).val($(this).val().replace(/[0-9]|[ \[\]{}()<>?|`~!@#$%^&*-_+=,.;:\"\\]/g, ""));
});


//무통장입금 체크값 확인
function Checkform() {
    if (frm.confirm.checked != true) {
        alert("결제 수단에 동의해주세요");
        frm.confirm.focus();
        return false;
    }
}


//text 입력값 확인
function Checkform() {
    if (frm.name.value == "") {
        frm.name.focus();
        alert("입금자명을 입력해주세요");
        return false;
    }
}


//qna 글쓰기 입력값 확인
function wrSend() {


    if (form.title.value == "") {
        form.title.focus();
        alert("제목을 입력하세요.");
        return false;
    } else if(form.name.value == "") {
        form.name.focus();
        alert("작성자를 입력하세요.");
        return false;
    } else if(form.contents.value == "") {
        form.contents.focus();
        alert("내용을 입력하세요.");
        return false;
    } else if(form.pw.value == "") {
        form.pw.focus();
        alert("비밀번호를 입력하세요.");
        return false;
    } else if(form.captcha.value == "") {
        form.captcha.focus();
        alert("이미지 글자를 입력하세요.");
        return false;
    }

    //글자수 확인
    if(form.title.value.length > 50) {
        form.title.focus();
        alert("제목 글자 수가 초과되었습니다.(50자 미만)");
        return false;
    } else if (form.name.value.length > 10) {
        form.name.focus();
        alert("작성자 글자 수가 초과되었습니다.(10자 미만)");
        return false;
    } else if (form.contents.value.length > 1500) {
        form.contents.focus();
        alert("내용 글자 수가 초과되었습니다.(1500자 미만)");
        return false;
    } else if (form.pw.value.length != 4) {
        form.pw.focus();
        alert("비밀번호는 4자리입니다.");
        return false;
    } else if (form.captcha.value.length != 5) {
        form.captcha.focus();
        alert("이미지 글자는 5자리입니다.");
        return false;
    } 

    return true;
}




//(function ($) {
//  "use strict";
//
//
//  // Back to top button
//  $(window).scroll(function() {
//    if ($(this).scrollTop() > 100) {
//      $('.back-to-top').fadeIn('slow');
//    } else {
//      $('.back-to-top').fadeOut('slow');
//    }
//  });
//  $('.back-to-top').click(function(){
//    $('html, body').animate({scrollTop : 0},1500, 'easeInOutExpo');
//    return false;
//  });
//
//})(jQuery);

function depositConfirm() {

    let confirm_1 = '';
    let confirm_2 = '';

    if(global_type == 'qr' && timerID != '') {

        
        if (click_type == 0) {
            confirm_1 = confirm("<주의>\n 입금확인이 될 때까지 본 화면을 닫지 마세요. \n 입금 전 본 화면을 닫으면 구매 신청이 자동 취소됩니다. \n\n 구매 신청을 취소하시겠습니까?");
        } else {
            confirm_2 = confirm("확인을 누르면 구매 조회 페이지로 이동합니다.");
        }

        
        if (confirm_1 == true) {
            $.ajax({
                url: './action/purchase_cancel.php',
                type: 'POST',
                data: {'seq_no':cond_no},
                dataType: 'json',
                success: function (data) {
                    if (data.status == 1) {
                        location.replace("process.php");
                    } else {
                        alert("처리상태 수정 실패");
                        location.replace("process.php");
                    }
                },
                error: function (err) {
                    console.log("just only error!! : " + err);
                }
            });
        } else if (confirm_2 == true) {
            location.replace("process.php");
        } else {

        }

    } else {
        location.replace("process.php");
    }
    
}


function check_mobile(){
    var currentOS;
    var mobile = (/iphone|ipad|ipod|android/i.test(navigator.userAgent.toLowerCase()));
    if(mobile) {
        var userAgent = navigator.userAgent.toLowerCase();
        if(userAgent.search("android") > -1) {
            return currentOS = "android";
        } else if((userAgent.search("iphone") > -1) || (userAgent.search("ipod") > -1) || (userAgent.search("ipad") > -1)) {
            return currentOS = "ios";
        } else {
            return currentOS = "else";
        }
    } else {
        return currentOS = "pc";
    }
}


function action_app(token){

    
    let device = check_mobile();
    if(device=="pc")
    {
        alert("not mobile");
        return false;
    }
    else if(device == "android")
    {

        // location.href = "multipayapp://multipay.co.kr?mode=1&token="+token+"/#Intent;package=com.wellcomad.multipay;scheme=https;end";

        // setTimeout( function() {
        //     location.href = "https://play.google.com/store/apps/details?id=com.wellcomad.multipay";
        // }, 1500);
        location.replace("multipayapp://multipay.co.kr?mode=1&token="+token);
    }
    else if(device == "ios")
    {
        // setTimeout( function() {
        //     location.href = "https://apps.apple.com/app/%EB%A9%80%ED%8B%B0%ED%8E%98%EC%9D%B4/id1611127090?platform=iphone";
        // }, 1500);
        location.replace("multipayappios://multipay.co.kr?mode=1&token="+token);

    }
    
}


function go_store(){

    let device = check_mobile();
    if(device=="pc")
    {
        window.open("https://play.google.com/store/apps/details?id=com.wellcomad.multipay");
    }
    else if(device == "android")
    {
        window.open("https://play.google.com/store/apps/details?id=com.wellcomad.multipay");
    }
    else if(device == "ios")
    {
        window.open("https://apps.apple.com/app/%EB%A9%80%ED%8B%B0%ED%8E%98%EC%9D%B4/id1611127090?platform=iphone");

    }
}



function getCookie(cName) {
    cName = cName + '=';
    var cookieData = document.cookie;
    var start = cookieData.indexOf(cName);
    var cValue = '';
    if(start != -1){
    start += cName.length;
    var end = cookieData.indexOf(';', start);
    if(end == -1)end = cookieData.length;
    cValue = cookieData.substring(start, end);
    }
    return unescape(cValue);
}