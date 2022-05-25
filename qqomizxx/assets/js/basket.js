

function depositStatus(e) {
    
   
    if(!confirm("처리 상태를 변경하시겠습니까?")) {
        alert("취소하였습니다.");
        location.reload();
    }
    else {

        //AJAX
        let sendData = new Object();
        sendData.reqVal = e.value;

        $.ajax({
            url: '../qqomizxx/action/userStatusChange.php',
            type: 'POST',
            data: {
                "sendData": sendData
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    alert(data.data);
                    location.reload();
                } else {
                    alert(data.msg);
                    location.reload();
                }
            },
            error: function (err) {
                console.log("just only error!! : " + err);
            }
        });
    }

}

function sendQR(seqno) {

    if(!confirm("QR코드를 구매완료 처리하시겠습니까?")) {
        alert("취소하였습니다.");
        location.reload();
    } else {

        $.ajax({
            url: '../qqomizxx/action/qrPurchase.php',
            type: 'POST',
            data: {"seqNo": seqno},
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    alert(data.data);
                    location.reload();
                } else {
                    alert(data.msg);
                    location.reload();
                    // let sendData = new Object();
                    // sendData.chainpincode = data.chainpincode;
                    // sendData.token = data.token;
                    // sendData.phoneno = data.phoneno;
                    // sendData.groupno = data.groupno;
                    // qr_update(sendData, 'qr');
                }
            },
            error: function (err) {
                console.log("just only error!! : " + err);
            }
        });
    }

}


function sendSMS(seqno, username, totalprice, bankname) {

    if(!confirm("SMS를 구매완료 처리하시겠습니까?")) {
        alert("취소하였습니다.");
        location.reload();
    } else {

        $.ajax({
            url: '../qqomizxx/action/qrPurchase.php',
            type: 'POST',
            data: {
                "adminSMS": "true",
                "seqNo": seqno,
                "userName": username,
                "totalPrice": totalprice,
                "bankName": bankname
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    alert(data.data);
                    location.reload();
                } else {
                    // alert(data.msg);
                    // location.reload();
                    let sendData = new Object();
                    sendData.chainpincode = data.chainpincode;
                    sendData.token = data.token;
                    sendData.phoneno = data.phoneno;
                    sendData.groupno = data.groupno;
                    qr_update(sendData, 'sms');
                }
            },
            error: function (err) {
                console.log("just only error!! : " + err);
            }
        });
    }

}

function qr_update(sendData, kind) {

    let action = ''; //cokoa
    if(kind == 'qr') {
        action = 'qr_update';
    } else if(kind == 'sms'){
        action = 'sms_update';
    }

    $.ajax({
        url: API_URL, 
        type: 'POST',
        data: {
            "action":action, //cokoa
            "auth":API_AUTH_TOKEN,
            "sendData": sendData,
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 0) {
                qr_fail_tran(data.data, data.groupno, sendData, data.msg);
                alert(data.msg);
                location.reload();
            } else {
                if(kind == 'qr') {
                    alert(data.msg);
                    location.reload();
                } else if(kind == 'sms') {
                    sms_purchase(JSON.stringify(data));
                }
                
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
        }
    });
}

function sms_purchase(send_json) { // cokoa
    $.ajax({
        url: '../qqomizxx/action/smsPurchase.php', 
        type: 'POST',
        data: {
            "json":send_json
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 0) {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg);
                location.reload();
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
        }
    });
}

function qr_fail_tran(failpincode, groupno, fail_param, fail_msg) {

    $.ajax({
        url: '../qqomizxx/action/qrPurchase.php',
        type: 'POST',
        data: {
            "failPinCode": failpincode,
            "groupNo": groupno,
            "fail_param": fail_param,
            "fail_msg": fail_msg
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 0) {
                alert(data.data);
                location.reload();
                return;
            } else {
                // alert(data.msg);
                location.reload();
                return;
            }
        },
        error: function (err) {
            console.log("just only error!! : " + err);
        }
    });
}


function admin_wrSend() {
    if(form.contents.value == "") {
        form.contents.focus();
        alert("내용을 입력하세요.");
        return false;
    }

    return true;
}

function reply_del(url, flag) {
    if(!confirm("답글을 삭제하시겠습니까?")) {
        alert("취소하였습니다.");
        return false;
    } else {
        if(flag == 1) {
            alert("답글이 없습니다.");
            return false;
        }
        location.replace(url);
    }
    
}


function post_del(url) {
    if(!confirm("게시물을 삭제하시겠습니까?")) {
        alert("취소하였습니다.");
        return false;
    } else {
        location.replace(url);
    }
    
}

function upWebState(e) {
    if(!confirm("처리 상태를 변경하시겠습니까?")) {
        alert("취소하였습니다.");
        location.reload();
    }
    else {

        let form_data = {
            action: 'web_view', 
            use_flag: e.value 
        }; 

        $.ajax({
            type: "POST",
            url: "./action/productcontroller.php",
            data: form_data,
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.status == 1) {
                    alert('변경되었습니다.');
                    window.location.reload();
                } else {
                    alert(obj.message);
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


function inWebState(e) {
    if(!confirm("등록하시겠습니까?")) {
        alert("취소하였습니다.");
    }
    else {
        
    }
}

function updateState() {}