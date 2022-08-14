<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__Html2TextService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__VerotUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use ScssPhp\ScssPhp\Compiler;

class FileService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->uploadsDir = $this->please->serve('dir')->dirPath('public'.DIRECTORY_SEPARATOR.'uploads');
    }

    public function getImageSrcRec($src): array
    {
        // Ex: http://localhost/lesboutika2bf.com/uploads/2020/09/img-20200923-wa0083--5f6f--1080x810.jpg
        preg_match('/([a-z-0-9]+)(--)([0-9]+)(x)([0-9]+)(.)([a-z]+)/', $src, $m);
        if(sizeof($m)===8){
            return ['x'=>$m[3], 'y'=>$m[5]];
        }
        return ['x'=>0, 'y'=>0];
    }

    public function uploadImage($params)
    {
        $timeServ = $this->please->serve('time');

        //$this->relPath = $timeServ->getDateTime()->format('Y') . '/' . $timeServ->getMonth(null, 'number');
        //$this->dirPath = $this->uploadsDir . '/' . $this->relPath;
        $this->dirPath = $this->uploadsDir . '/';
        
        $image_ratio_crop = $params['imageRatioCrop'] ?? true;
        $params = array_merge([
            'inputName' => null,
            'fileName' => uniqid(),
            'x' => 100, 'y' => 100,
            'dirPath' => $this->dirPath,
            'settings' => function($file) use ($params, $image_ratio_crop){

                $img_prop = getimagesize($_FILES[$params['inputName']]['tmp_name']);

                $file->image_x = $params['x'] ?? $img_prop[0];
                $file->image_y = $params['y'] ?? $img_prop[1];
                $file->image_convert = 'webp';
                $file->image_is_transparent = true;
                $file->file_max_size = '1G';
                $file->allowed = 'image/*';
                $file->image_ratio_fill = true;
                $file->image_background_color = false;
                $file->image_ratio_crop = $image_ratio_crop;
                $file->image_resize = true;
                $file->image_ratio = true;
                $file->image_resize = true;

                //
                return $file;
             },
            'onSuccess' => function ($params) {},
            'onError' => function ($err) {},
        ], $params);

        if (isset($_FILES[$params['inputName']])) {

            $file = new __VerotUploadService($_FILES[$params['inputName']]);

            if ($file->uploaded) {

                if( exif_imagetype($file->file_src_pathname) === IMAGETYPE_WEBP ){
                    $img = imagecreatefromwebp($file->file_src_pathname);
                    $file->image_dst_x = imagesx($img);
                    $file->image_dst_y = imagesy($img);
                }

                $settings = $params['settings']($file);
                $filename = $params['fileName'] ?? $file->file_src_name_body;
                $file->file_new_name_body = (new \DateTime())->format('dmY') .
                                            '-' . $filename .
                                            '-' . ($file->image_dst_x ?? $params['x']).'x'.($file->image_dst_y ?? $params['y']);

                //lets ensure we unlink the file if already exists on the same name
                if (file_exists($file_path_to_unlink = $params['dirPath'] . DIRECTORY_SEPARATOR . $file->file_new_name_body . '.' . $file->image_convert)) {
                    unlink($file_path_to_unlink);
                }

                $file->process($params['dirPath']);

                if ($file->processed) {
                    //$file->clean();
                    $p['extendedFilename'] = $file->file_dst_name_body . "." . $file->file_dst_name_ext;
                    $p['filename'] = $file->file_dst_name_body;
                    //$relativePath = 'uploads/' . $this->relPath . '/'. $p['extendedFilename'];
                    $relativePath = str_replace($this->dirPath, 'uploads/', $params['dirPath']) . '/'. $p['extendedFilename'];
                    $p['extension'] = $file->file_dst_name_ext;
                    $p['relativeUrl'] = trim(preg_replace('~/+~', '/', $relativePath), '/');
                    $p['absoluteUrl'] = $this->please->serve('url')->getUrl($p['relativeUrl']);
                    $p['x'] = (int)$file->image_dst_x;
                    $p['y'] = (int)$file->image_dst_y;
                    $p['dirPath'] = $params['dirPath'] . '/'. $p['extendedFilename'];
                    $p['fileSrcSize'] = $file->file_src_size;
                    $p['isImage'] = true;
                    return $params['onSuccess']((object)$p);
                } else return $params['onError']('Cant process the image');
            } else return $params['onError']('Cant upload the image');
        }
        return $params['onError']('Cant find inputName');
    }

    public function uploadFile($params)
    {
        $timeServ = $this->please->serve('time');
        $this->relPath = $timeServ->getDateTime()->format('Y') . '/' . $timeServ->getMonth(null, 'number');
        $this->dirPath = $this->uploadsDir . '/' . $this->relPath;

        $params = array_merge([
            'inputName' => null,
            'fileName' => uniqid(),
            'dirPath' => $this->dirPath,
            'onSuccess' => function () {},
            'onError' => function ($err) {},
        ], $params);

        if (isset($_FILES[$params['inputName']])) {

            $file = new __VerotUploadService($_FILES[$params['inputName']]);

            if( $this->isImage($file->file_src_pathname) ){
                return $this->uploadImg($params);
            }

            if ($file->uploaded) {

                $file->file_is_image = false;
                $filename = !is_null($params['fileName']) ? $params['fileName'] : $file->file_src_name_body;

                //lets ensure we unlink the file if already exists on the same name
                if (file_exists($file_path_to_unlink = $params['dirPath'] . DIRECTORY_SEPARATOR . $file->file_new_name_body . '.' . $file->file_name_ext)) {
                    unlink($file_path_to_unlink);
                }

                $file->process($params['dirPath']);

                if ($file->processed) {
                    //$file->clean();
                    $p['extendedFilename'] = $file->file_dst_name_body . "." . $file->file_name_ext;
                    $p['filename'] = $file->file_dst_name_body;
                    $relativePath = 'uploads/' . $this->relPath . '/'. $p['extendedFilename'];
                    $p['extension'] = $file->file_name_ext;
                    $p['relativeUrl'] = trim(preg_replace('~/+~', '/', $relativePath), '/');
                    $p['absoluteUrl'] = $this->please->serve('url')->getUrl($p['relativeUrl']);
                    $p['dirPath'] = $params['dirPath'] . '/'. $p['extendedFilename'];
                    $p['fileSrcSize'] = $file->file_src_size;
                    $p['isImage'] = false;
                    return $params['onSuccess']((object)$p);
                } else return $params['onError']('Cant process the image');
            } else return $params['onError']('Cant upload the image');
        }
        return $params['onError']('Cant find inputName');
    }

    public function getFileName($originalName)
    {
        return str_ireplace('.' . $this->getFileExtension($originalName), '', $originalName);
    }

    public function getFileExtension($originalName)
    {
        $exploded = explode('.', $originalName);
        return end($exploded);
    }
    
    public function isImage($path)
    {
        if( !empty($path) ){
            $a = getimagesize($path);
            $image_type = $a[2];
            if(in_array($image_type , array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
                return true;
            }
        }
        return false;
    }
    
    public function getFileInfo($src)
    {
        $filePath = $this->please->serve('dir')->getProjectPath(
            str_replace($this->please->serve('env')->getAppBaseUrl(), '', $src)
        );
        $pathinfo = pathinfo($filePath);
        return (object) array_merge([
            'absoluteUrl' => $src,
            'isImage' => $this->isImage($src),
            'isVideo' => in_array($pathinfo['extension'], ['mp4']),
            'isAudio' => in_array($pathinfo['extension'], ['mp3'])
        ], $pathinfo);
    }
}
