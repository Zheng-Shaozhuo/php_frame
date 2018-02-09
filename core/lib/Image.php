<?php
/**
 * Created by PhpStorm.
 * User: Zheng-Shaozhuo
 * Date: 2018/2/9
 * Time: 16:49
 */
namespace core\lib;

header("content-type:text/html;charset=utf-8");

class Image
{
    function __construct(){
    }

    public static function verifyImage($width = 80, $height = 28, $type = 1, $len = 4, $sess_name = 'verify', $pixels = 0, $lines = 0) {
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $prColor = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 1, 1, $width - 2, $height - 2, $bgColor);
        $verifyCode = String::randString($type, $len);
        $_SESSION[$sess_name] = $verifyCode;

        $fontfile = "";
        for ($i = 0; $i < $len; $i++) {
            $size = mt_rand($height - 14, $height - 8);
            $angle = mt_rand(-30, 30);
            $x = mt_rand(4, 7) + $i * $size;
            $y = mt_rand($height - 10, $height - 4);
            $textColor = imagecolorallocate($image, mt_rand(40, 100), mt_rand(80, 200), mt_rand(100, 200));
            $text = substr($verifyCode, $i, 1);
            imagettftext($image, $size, $angle, $x, $y, $textColor, $fontfile, $text);
        }

        if ($pixels) {
            for ($i = 0; $i < $pixels; $i++) {
                $color = imagecolorallocate($image, mt_rand(40, 100), mt_rand(80, 200), mt_rand(100, 200));
                imagesetpixel($image, mt_rand(0, $width - 1), mt_rand(0, $height - 1), $color);
            }
        }

        if ($lines) {
            for ($i = 0; $i < $lines; $i++) {
                $color = imagecolorallocate($image, mt_rand(40, 100), mt_rand(80, 200), mt_rand(100, 200));
                imageline($image, mt_rand(40, 100), mt_rand(80, 200), mt_rand(40, 100), mt_rand(80, 200), $color);
            }
        }

        header("content-type: image/jpeg");
        imagejpeg($image);
        imagedestroy($image);
    }
}