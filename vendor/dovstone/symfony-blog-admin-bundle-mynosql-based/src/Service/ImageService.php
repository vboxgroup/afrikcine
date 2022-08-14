<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;

class ImageService extends AbstractController
{
    private $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function resize($src, $newWidth = null, $newHeight = null, $mode='r', $bgColor = [255, 255, 255]): string
    {
        //if( !$src ){ return $this->please->serve('asset')->getLogoSrc(); }
        if( !$src ){ return $this->please->serve('asset')->getAsset($this->please->serve('env')->getAppEnv('LAZY_LOAD_IMAGES')); }

        if( !file_exists($pathname = $this->please->serve('dir')->dirPath('public/uploads-sizes')) ){ mkdir($pathname); }

        if( $mode === 'c' ){
            return $this->crop($src, $newWidth, $newHeight, $bgColor);
        }
        
        $dst_image = urldecode($this->getDst($src));

        if( !file_exists($dst_image) ){ return $src; }

        list($w, $h) = getimagesize($dst_image);

        $nw = $newWidth ?? $w;
        $nh = $newHeight ?? $h;

        $ow = $nw;
        $oh = $nh;

        $ratio = $w / $h;

        if ( $nw / $nh > $ratio ) { $nw = $nh * $ratio; }
        else { $nh = $nw / $ratio; }

        $dst_x = ($ow - $nw) / 2;
        $dst_y = ($oh - $nh) / 2;

        $nw = (int) $nw;
        $nh = (int) $nh;

        $src_image = $this->getLocalSource($dst_image, 'r', $ow, $oh);

        if( !file_exists($dst_image) ){
            return '#';
        }
        // check if resized image already exists
        if( file_exists($this->getLocalSource( $dst_image, 'r', $nw, $nh )) ){
            return $this->getHttpSource( $src_image, $ow, $oh );
        }

        $thumb = imagecreatetruecolor($ow, $oh);

        if( $bgColor == 'transparent' ){
            imagesavealpha($thumb, true);
            imagealphablending($thumb, false);
            $bgColor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        }
        elseif( is_array($bgColor) && count($bgColor) === 3 ) {
            $bgColor = imagecolorallocate($thumb, $bgColor[0] ?? 255, $bgColor[1] ?? 255, $bgColor[2] ?? 255);
        }
        else {
            $bgColor = imagecolorallocate($thumb, 0, 0, 0);
        }

        if(exif_imagetype($dst_image) === IMAGETYPE_WEBP){
            // imagefill($thumb, 0, 0, $bgColor);
            // $image = imagecreatefromwebp($dst_image);
            // imagecopyresampled($thumb, $image, $dst_x, $dst_y, 0, 0, $nw, $nh, $w, $h);
            // imagewebp($thumb, $src_image);
            // imagedestroy($thumb);
        }
        else if(exif_imagetype($dst_image) === IMAGETYPE_JPEG){
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefromjpeg($dst_image);
            imagecopyresampled($thumb, $image, $dst_x, $dst_y, 0, 0, $nw, $nh, $w, $h);
            imagejpeg($thumb, $src_image);
            imagedestroy($thumb);
        }
        else if(exif_imagetype($dst_image) === IMAGETYPE_GIF){
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefromgif($dst_image);
            imagecopyresampled($thumb, $image, $dst_x, $dst_y, 0, 0, $nw, $nh, $w, $h);
            imagegif($thumb, $src_image);
            imagedestroy($thumb);
        }
        else {
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefrompng($dst_image);
            imagecopyresampled($thumb, $image, $dst_x, $dst_y, 0, 0, $nw, $nh, $w, $h);
            imagepng($thumb, $src_image);
            imagedestroy($thumb);
        }

        return $this->getFinalHttpSource( $dst_image, 'r', $ow, $oh );
    }

    private function crop($src, $newWidth = null, $newHeight = null, $bgColor = [255, 255, 255])
    {
        $dst_image = urldecode($this->getDst($src));

        if( !file_exists($dst_image) ){ return $src; }

        list($xx, $yy) = getimagesize($dst_image);
        
        $nw = $newWidth ?? $w;
        $nh = $newHeight ?? $h;
        
        $ow = $nw;
        $oh = $nh;
        
        $ratio_thumb = $nw / $nh;

        $ratio = $xx / $yy;

        if ($ratio >= $ratio_thumb) {
            $yo = $yy; 
            $xo = ceil(($yo * $nw) / $nh);
            $xo_ini = ceil(($xx - $xo) / 2);
            $xy_ini = 0;
        } else {
            $xo = $xx; 
            $yo = ceil(($xo * $nh) / $nw);
            $xy_ini = ceil(($yy - $yo) / 2);
            $xo_ini = 0;
        }
        
        //$src_image = $this->getLocalSource($dst_image, $xo, $yo);
        $src_image = $this->getLocalSource($dst_image, 'c', $ow, $oh);
        
        if( !file_exists($dst_image) ){
            return '#';
        }
        // check if cropped image already exists
        if( file_exists($this->getLocalSource( $dst_image, 'c', $nw, $nh )) ){
            return $this->getHttpSource( $src_image, $ow, $oh );
        }

        $thumb = imagecreatetruecolor($ow, $oh);
        
        if( $bgColor == 'transparent' ){
            imagesavealpha($thumb, true);
            imagealphablending($thumb, false);
            $bgColor = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        }
        elseif( is_array($bgColor) && count($bgColor) === 3 ) {
            $bgColor = imagecolorallocate($thumb, $bgColor[0] ?? 255, $bgColor[1] ?? 255, $bgColor[2] ?? 255);
        }
        else {
            $bgColor = imagecolorallocate($thumb, 0, 0, 0);
        }

        if(exif_imagetype($dst_image) === IMAGETYPE_WEBP){
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefromwebp($dst_image);
            imagecopyresampled($thumb, $image, 0, 0, $xo_ini, $xy_ini, $nw, $nh, $xo, $yo);
            imagewebp($thumb, $src_image);
            imagedestroy($thumb);
        }
        elseif(exif_imagetype($dst_image) === IMAGETYPE_JPEG) {
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefromjpeg($dst_image);
            imagecopyresampled($thumb, $image, 0, 0, $xo_ini, $xy_ini, $nw, $nh, $xo, $yo);
            imagejpeg($thumb, $src_image);
            imagedestroy($thumb);
        }
        elseif(exif_imagetype($dst_image) === IMAGETYPE_GIF) {
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefromgif($dst_image);
            imagecopyresampled($thumb, $image, 0, 0, $xo_ini, $xy_ini, $nw, $nh, $xo, $yo);
            imagegif($thumb, $src_image);
            imagedestroy($thumb);
        }
        else {
            imagefill($thumb, 0, 0, $bgColor);
            $image = imagecreatefrompng($dst_image);
            imagecopyresampled($thumb, $image, 0, 0, $xo_ini, $xy_ini, $nw, $nh, $xo, $yo);
            imagepng($thumb, $src_image);
            imagedestroy($thumb);
        }

        return $this->getFinalHttpSource( $dst_image, 'c', $ow, $oh );
    }

    private function getHttpSource( $dst_image, $w, $h )
    {
        return $this->please->serve('url')->getUrl(preg_replace('/(.*)(uploads-sizes)(.*)/m', '$2$3', $dst_image));
    }

    private function getDst( $dst_image )
    {
        return $this->please->serve('dir')->dirPath('public/' . preg_replace('/(.*)(uploads)(.*)/m', '$2$3', $dst_image));
    }

    private function getLocalSource( $dst_image, $mode, $nw, $nh )
    {
        $uploadsPath = preg_replace('/(.*)(public\/uploads)(.*)/m', "$1public/uploads-sizes", $dst_image);
        $xploded = explode('/', $dst_image); $filename = end($xploded);
        $dst_image = $uploadsPath . '/' . $filename;
        //$src = preg_replace('/(.*)(-)([0-9]+)(x)([0-9]+)(.*)/m', "$1-{$nw}x{$nh}$6", $dst_image);
        $src = preg_replace('/(.+)(.)(jpg|jpeg|png|gif|webp|bmp)/m', "$1-{$mode}-{$nw}x{$nh}.$3", $dst_image);
        return $src;
    }

    private function getFinalHttpSource( $dst_image, $mode, $nw, $nh )
    {
        $src = $this->getLocalSource( $dst_image, $mode, $nw, $nh );
        return $this->please->serve('url')->getUrl(preg_replace('/(.*)(uploads-sizes)(.*)/m', '$2$3', $src));
    }
}