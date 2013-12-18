<?php
/**
 * User: pmsun
 * Version: 1.0
 * Date: 13-12-18
 * Time: 下午8:24
 * Desc: 验证码类
 * Usage:
 *  $captcha = new Captcha(80, 40, 'font/5.ttf');
 *  $captcha->create();
 */

class Captcha {
    protected $width; //生成图片宽
    protected $height; //生成图片高
    protected $font; //字体路径
    protected $size; //字体大小
    protected $long; //验证码的长度

    public function __construct($width = 60, $height = 22, $font = "", $size = 5, $long = 4) {
        $this->width = $width;
        $this->height = $height;
        $this->font = $font;
        $this->size = $size === "" ? 12 : $size;
        $this->long = $long === "" ? 4 : $long;
        $this->expires = $expires === "" ? 7200 : $expires;
    }

    protected function code() {
        $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $word = "";
        for ($i = 0; $i < $this->long; $i++) {
            $num = mt_rand(0, strlen($str) - 1);
            $word .= $str[$num];
        }
        $_SESSION['captcha'] = $word;
        return $word;
    }

    public function create() {
        $image = imagecreate($this->width, $this->height); //定义画布

        //绘制文字
        $R = mt_rand(0, 255);
        $G = mt_rand(0, 255);
        $B = mt_rand(0, 255);

        if ($this->font === "") {
            $gray = ImageColorAllocate($image, 204, 204, 204);
            $textcolor = imagecolorallocate($image, $R, $G, $B);
            $position = $this->position();
            ImageString($image, 5, $position[0], $position[1], $this->code(), $textcolor); //使用默认字体
        } else {
            $gray = ImageColorAllocate($image, 255, 255, 255);
            $textcolor = imagecolorallocate($image, $R, $G, $B);
            imagettftext($image, 13, 0, 0, 30, $textcolor, $this->font, $this->code());
        }
        header("Content-type: image/png");
        return ImagePNG($image);
    }

    protected function position() {
        //默认字体，宽高的对比
        $width_arr = array(1 => 5, 6, 7, 8, 9);
        $height_arr = array(1 => 6, 8, 13, 15, 15);

        $xi = $this->width;
        $yi = $this->height;
        //计算文本大小
        if ($this->font === "") {
            $xr = $width_arr[$this->size] * $this->long;
            $yr = $height_arr[$this->size];
        } else {
            $xr = $this->size * $this->long;
            $yr = $this->size;
        }

        //计算中心位置
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi - $yi) / 2);
        return array($x, $y);
    }
}
