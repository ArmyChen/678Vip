<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(strpos($_REQUEST['src'],'/public/attachment') > 0){
        $src = str_replace('/./','',$_REQUEST['src']);
    }else{
        $src = $_REQUEST['src'];
    }
    $time = time();
    $date = date('Ymd',$time);
    $path =  "public/attachment/".$date;
    if(!file_exists($path)){
        mkdir($path);
    }
    $filename = $path."/".$time.".jpg";

    $targ_w = $targ_h = 900;
    $jpeg_quality = 90;

    $img_r = imagecreatefromjpeg($src);
    $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

    imagecopyresampled($dst_r,$img_r,0,0,$_GET['x'],$_GET['y'],
        $targ_w,$targ_h,$_GET['w'],$_GET['h']);

//    header('Content-type: image/jpeg');
    imagejpeg($dst_r, $filename, $jpeg_quality);



echo "./".$filename."";
    exit;
}