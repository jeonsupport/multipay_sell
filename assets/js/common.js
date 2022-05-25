// 창닫기 pop(key)
function closePop(key) {
    $('.pop' + key).hide();
    $('.bg').hide();
    $('main').removeClass('blur');

    if (key==1) {
        setCookie('pop_cookie', 'ok');
    }
};

// 220318 모바일 maxLength 막기
function maxLengthCheck(object){
	if(object.value.length > object.maxLength) {
		object.value = object.value.slice(0, object.maxLength);
	}
};



// header(m) - gnb
$('.btnNav').click(function () {
    $('.nav').fadeToggle('fast');
    // $('.gnb').toggleClass('bg');
    $('.ico_x,.ico_m').toggle();
});

// btn(pc)- top
$(document).ready(function(){ 
    $(window).scroll(function(){ 
        if ($(this).scrollTop() > 100) { 
            $('#scroll').fadeIn(); 
        } else { 
            $('#scroll').fadeOut(); 
        } 
    }); 
    $('#scroll').click(function(){ 
        $('html, body').animate({ scrollTop: 0 }, 500); 
        return false; 
    }); 
});



/* 쿠키 관련 함숫 */
function setCookie(cookie_name, value, days=365) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + days);
    // 설정 일수만큼 현재시간에 만료값으로 지정
    
    var cookie_value = escape(value) + ((days == null) ? '' : '; expires=' + exdate.toUTCString());
    document.cookie = cookie_name + '=' + cookie_value;
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

