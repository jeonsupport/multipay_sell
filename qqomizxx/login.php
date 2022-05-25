<!DOCTYPE html>
<html lang="en">
<head>
<title>관리자 로그인</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Language" content="ko">
<link rel="stylesheet" href="assets/css/base.css">
<style>
#login-form {width: 450px;background: #fff;padding: 120px 50px 150px;position:relative;margin:0 auto;}
#login-form h1 {margin:30px 0 60px;color: #333;font-size: 22px;line-height:1.2}
#login-form p {font-size: 16px;color: #333;}
#login-form p a {color: #00bcd4;}
#login-form label {color: #848484;}
.input-box{border-bottom: 2px solid #adadad;position: relative;margin: 30px 0;}
.input-box input{font-size: 15px;color: #333;border: none;width: 100%;outline: none;background: none;padding: 0 5px;height: 40px;}
.input-box span::after{content: '';position: absolute;width: 0;height: 2px;background: linear-gradient(120deg,#2196F3,#FF5722);transition: .5s;}
.focus + span::before{top: -5px;}
.focus + span::after{width: 100%;}
.login-btn {display: block;border:none;background: #333;color: #fff;cursor: pointer;transition: .5s;font-size: 15px;float:right;padding:10px 15px;}
.login-btn:hover{opacity: .8;}
.bottom-links{margin-top: 30px;text-align: center;font-size: 13px;}
::placeholder {color:#adadad;}
input[type="password"]{line-height: 28px;vertical-align: top;margin-top: 13px;}
@media screen and (max-width:500px) {
    #login-form{width:100%;margin:0;padding:10px 20px}
    .logoLog{}
    #login-form h1{font-size:20px;height:10%;margin:50px 0;}
    #login-form .input-box{margin:30px 0;font-size:20px;}
    #login-form .input-box input{font-size:15px;padding:0 5%;height:30px;}
    #login-form .login-btn{font-size:15px;margin-top:0;z-index: 99999999;}
    div.wrap_footer{border:none;margin: 50px 0}
    div.wrap_footer .footer{width:100%;}
    div.wrap_footer .footer .logo{display:none;}
    div.wrap_footer .footer ul{width:100%;}
}
input[type=text]:focus, input[type=password]:focus, input[type=tel]:focus, input[type=email]:focus, input[type=number]:focus, textarea:focus, select:focus, textarea:focus {background:#fafafa;}
.logoLog{height:50px;}
.logoLog img{height:100%;}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
    <form action="./action/login_action.php" id="login-form" method='post'>
        <div class='logoLog'><img src='assets/img/logo.png'></div>
        <h1>관리자 계정에 로그인합니다.</h1>
        <div class="input-box">
            <input name='id' type="text" placeholder="아이디"><span></span>
        </div>
        <div class="input-box">
            <input name='pwd' type="password" placeholder="비밀번호"><span></span>
        </div>
        <button type="submit" class="login-btn">로그인</button>
    </form>
</body>
</html>
