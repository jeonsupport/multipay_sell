<?php
    session_start();

    //이미지 크기
    $img = imagecreate(130,50);

    //캡챠 폰트 크기
    $size = 30;
    //캡챠 폰트 기울기
    $angle = 0;
    //캡챠 폰트 x,y위치
    $x = 10;
    $y = 40;
    //이미지의 바탕화면은 흰색
    $background = imagefill($img,0,0,imagecolorallocatealpha($img,255, 255, 255, 100));
    //폰트 색상
    $text_color = imagecolorallocate($img, 233, 14, 91);
    //폰트 위치
    $font = "/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf"; 

    //캡챠 텍스트
    $str = substr(md5(rand(1,10000)),0,5);
    //가입 시 캡챠 텍스트 확인을 위해 session에 담는다.
    $_SESSION['str'] = $str;

    //글자를 이미지로 만들기
    imagettftext($img,$size,$angle,$x,$y,$text_color,$font,$str);

    Header("Content-type: image/jpeg");
    imagejpeg($img,null,100);
    imagedestroy($img);
