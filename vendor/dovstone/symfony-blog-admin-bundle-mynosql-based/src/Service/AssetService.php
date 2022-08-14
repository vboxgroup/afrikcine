<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__VerotUploadService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__PhpHtmlCssJsMinifierService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__ImgCompressorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use ScssPhp\ScssPhp\Compiler;
use Twig\Markup;


class AssetService extends AbstractController
{
    protected $previousContainer;
    //
    private $filesystem;
    private $__PhpHtmlCssJsMinifierService;
    private $__ImgCompressorService;

    public $please;
    public $uploadsDir;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        //
        $this->filesystem = new Filesystem();
        $this->uploadsDir = $this->please->prevContainer->get('kernel')->getProjectDir() . "/public/uploads";
        $this->themeDir = $this->please->prevContainer->get('kernel')->getProjectDir() . "/theme";
    }
    
    public function getHeadAssets($arr=['bs-3','fa-5','animate','basic'], $reset=false) : string
    {
        $output = '<style>html .pending::before{background:rgba(255, 255, 255, 0.32) url("'. $this->getCDN('swagg/assets/img/loading-spinner.gif') .'") center center no-repeat}</style>'."\n";
        $output .= '<script src="'.$this->getCDN("swagg/services/addscript.min.js?v=").rand(0, 999).'"></script>'."\n";
        $output .= $this->getCDN([
			'owlcarousel/css/owl.carousel.min',
            'owlcarousel/css/owl.theme.default.min',
            'trumbowyg/css/trumbowyg.min',
            'jBox/jBox.all.min',
        ], 'css', $reset);
        $output .= $this->getCDN([

            'jquery/jquery-1.11.3.min',
            'less/less@3.13',
            'trumbowyg/js/trumbowyg.min',
            'trumbowyg/js/fr.min',
            'underscore/js/underscore',
            'iconify/js/iconify.min',
            'owlcarousel/js/owl.carousel.min',
            'sticky/jquery.sticky',
            'sweetalert2/js/sweetalert2.all.min',
            'jBox/jBox.all.min',
            'hoangnd25/cacheJS/cacheJS.min|v',

            'swagg/services/window|v',
            'swagg/helpers|v',
            'swagg/sleekdb-based/sfm--builder-mynosql-based|v',
            'swagg/services/gcontrol|v',
            'swagg/services/bindonce|v',
            'swagg/services/please|v',
            'swagg/services/ajaxify-v2|v',
            'swagg/services/lazyloading|v',
            'swagg/services/attr|v',
            'swagg/services/less-to-textless|v',
            'swagg/services/ping|v',
            'swagg/services/cute-modal|v',
            'swagg/services/blur-effect|v',
            'swagg/services/iconifier|v',
			'swagg/services/replacenode|v',
			'swagg/services/sticky|v',
            'swagg/services/nav-active|v',
            'swagg/services/files-gallery|v',
            'swagg/services/generic-app-init|v',
        ], 'js', $reset);

        $cssBag = [
            'bs-3' => 'bootstrap-3/css/bootstrap.min|v',
            'bs-4' => 'bootstrap-4/css/bootstrap.min|v',
            'bs-5' => 'bootstrap-5/css/bootstrap.min|v',
            'fa-4' => 'font-awesome-4/css/cdn-origin.font-awesome.min|v',
            'fa-5' => 'font-awesome-5/css/cdn-origin.all|v',
            'imports' => 'swagg/assets/css/imports|v',
            'animate' => 'animate/css/animate.min|v',
            'basic' => 'swagg/assets/css/basic|v',
            'tabs' => 'swagg/assets/css/tabs|v',
            'tiles' => 'swagg/assets/css/tiles|v'
        ];
        $finalCssBag = [];

        foreach($arr as $assetKey){
            if( array_key_exists($assetKey, $cssBag) ){
                $finalCssBag[] = $cssBag[$assetKey];
            }
        }

        $output .= $this->getCDN($finalCssBag, 'css', $reset);

        return new Markup($output, 'UTF-8');
    }
    
    public function getThemeAssets(array $paths=[], $extension='css')
    {
        if( in_array($extension, ['css', 'js']) ){
            return $this->getMixedAssets($paths, $extension);
        }
        return null;
    }
    
    public function getLogoSrc($v=1)
    {
        return  $this->getAsset('logo.png') . "?v=$v";
    }
    
    public function getAsset($asset, $attr='')
    {
        $urlServ = $this->please->serve('url');
        $dirServ = $this->please->serve('dir');

        if(strpos($asset, '.css') !== false){
            $suffix = "css/$asset";
            $isCss = true;
        }
        elseif (strpos($asset, '.js') !== false) {
            $suffix = "js/$asset";
            $isJs = true;
        }
        else {
            $suffix = "img/$asset";
            $isImage = true;
        }

        if(strpos($suffix, '|v')!==false){
            $s = str_replace('|v', '', $suffix);
            $data_url = $s;
            $suffix = $s.'?v='.rand();
        }
        else {
            $s = $suffix;
            $data_url = $s;
        }

        $s = str_ireplace(['css','js','.','/'], ['_','_','_','_'], $s);
        $url = $urlServ->getUrl("assets/".$suffix);
        $data_url = $urlServ->getUrl("assets/".$data_url);

        if(isset($isCss)){
            return new Markup('<link data-ref="'.$data_url.'" id="link_'.$s.'" '.$attr.' rel="stylesheet" href="'.$url.'">', 'UTF-8');
        }
        else if(isset($isJs)) {
            return new Markup('<script defer="true" data-ref="'.$data_url.'" id="script_'.$s.'" '.$attr.' src="'.$url.'"></script>', 'UTF-8');
        }
        else return $url;
    }

    public function getCDN($path, $extension='css', $reset=false)
    {
        $urlServ = $this->please->serve("url");
        $dirServ = $this->please->serve("dir");

        if( $reset === true ){
            $filesystem = new FileSystem();
            $dir = $dirServ->dirPath('public/assets/css-js');
            if( !isset($this->cssJsRemoved) ){
                $filesystem->remove($dir);
                $filesystem->mkdir($dir);
            }
            $this->cssJsRemoved = true;
        }

        if( is_string($path) ){
            return new Markup(trim($this->please->serve('env')->getAppEnv('CDN_ORIGIN'), '/'). '/' . trim(preg_replace('~/+~', '/', $path), '/'), 'UTF-8');
        }
        elseif(is_array($path)){

            $paths = $path;

            if( !isset($this->minifier) ){
                $this->minifier = new __PhpHtmlCssJsMinifierService();
            }
            
            $cssContents = $jsContents = '';

            $mixedKey = md5(json_encode($paths));
            $mixedCssFilepath = $dirServ->dirPath("public/assets/css-js/".$mixedKey.".min.css");
            $mixedJsFilepath = $dirServ->dirPath("public/assets/css-js/".$mixedKey.".min.js");

            foreach($paths as $i => $path){
                //$v = strpos($path, '|v') !== false ? '?v='.rand(0, 999) : '';
                $v = '?v='.rand(0, 999);
                $path = str_replace('|v', '', $path);
                $id = str_ireplace(['.css','.js','.','/', '-'], ['_','_','_','_','_'], $path);
                if( stripos($path, 'http') === false ){
                    $path = $this->getCDN($path.'.'.$extension);
                }
                else {
                    $path = $path.'.'.$extension;
                }

                $limit = sizeof($paths)-1;
                
                switch($extension){
                    case 'css':
                            if( !file_exists($mixedCssFilepath) ){ $cssContents .= file_get_contents($path.$v) . PHP_EOL; }
                            if( $i == $limit ){
                                if(!file_exists($mixedCssFilepath)){
                                    $content = str_ireplace('CDN_ORIGIN', $this->please->serve('env')->getAppEnv('CDN_ORIGIN'), $this->minifier->getMinifiedCss($cssContents));
                                    file_put_contents($mixedCssFilepath, $content, FILE_APPEND | LOCK_EX);
                                }
                                //
                                $cssOutput = '<style data-links=\''.json_encode($paths).'\'>'.file_get_contents($urlServ->getUrl("assets/css-js/".$mixedKey.".min.css?v=".rand())).'</style>'."\n";
                                return new Markup($cssOutput, 'UTF-8');
                            }
                        break;
                    case 'js' : 
                            if( !file_exists($mixedJsFilepath) ){ $jsContents .= file_get_contents($path.$v) . PHP_EOL; }
                            if( $i == $limit ){
                                if(!file_exists($mixedJsFilepath)){
                                    file_put_contents(
                                        $mixedJsFilepath,
                                        $jsContents,
                                        FILE_APPEND | LOCK_EX
                                    );
                                }
                                $jsOutput = '<script defer="true" data-links=\''.json_encode($paths).'\' src="'.$urlServ->getUrl("assets/css-js/".$mixedKey.".min.js?v=".rand()).'"></script>'."\n";
                                return new Markup($jsOutput, 'UTF-8');
                            }
                        break;
                    default : $output = '<img data-url="'.$path.'" id="img_'.$id.'" src="'.$path.$v.'">' ."\n";
                }
            }
        }
        return '';
    }
    
    public function getAppCss($reset = false)
    {
        // $dirServ = $this->please->serve('dir');

        // $fs = new FileSystem();

        // $scssContents = file_get_contents( $dirServ->dirPath('public/assets/scss/app.scss') );

        // $key = md5('app');
        // $mixedCssFilepath = $dirServ->dirPath("public/assets/css-js/$key.min.css");

        // if( $reset === true ){
        //     $fs->remove($mixedCssFilepath);
        // }

        // $scssContentsMd5 = $this->please->getGlobal('scssContentsMd5');
        // if( $scssContentsMd5 && $scssContentsMd5 != md5($scssContents) ){
        //     $fs->remove($mixedCssFilepath);
        // }
        // //
        // if (!$fs->exists($mixedCssFilepath)) {
        //     //$fs->appendToFile( $mixedCssFilepath, (new __PhpHtmlCssJsMinifierService())->getMinifiedCss((new Compiler())->compile($scssContents)) );
        //     $this->please->setGlobal(['scssContentsMd5' => md5($scssContents) ]);
        // }
        // //
        // //$cssOutput = "<style>".file_get_contents($this->please->serve("url")->getUrl("assets/css-js/$key.min.css"))."</style>"."\n";
        // $cssOutput = "<style>".file_get_contents($mixedCssFilepath)."</style>"."\n";
        // return new Markup($cssOutput, 'UTF-8');
    }
    
    public function getLess(array $files = [])
    {
        $dirServ = $this->please->serve('dir');

        require $dirServ->dirPath("vendor/leafo/lessphp/lessc.inc.php");
        $less = new \lessc;

        $complied = '';
        $files = array_merge($files, ['app']);
        foreach ($files as $filename) {
            $input = $dirServ->dirPath("public/assets/less/$filename.less");
            $output = $dirServ->dirPath("public/assets/css-js/$filename.css");
            $less->checkedCompile($input, $output);
            $complied .= '<style data-name="'.$filename.'">'.file_get_contents($output).'</style>' . "\n";
        }
        return new Markup($complied, 'UTF-8');
    }
    
    public function getAppConfig($jsonify = true)
    {
        $post = $this->please->getGlobal('post');
        $envServ = $this->please->serve('env');
        $urlServ = $this->please->serve('url');

        $sfmDirname = '';
        foreach ([1,2,3,4,5] as $v) {
            $name = 'sfm-v' . $v;
            if( file_exists($filename = $this->please->serve('dir')->dirPath($name)) ){
                $sfmDirname = $name;
                continue;
            }
        }

        $appConfig = [
            'post_id' => attr($post, 'id'),
            'type' => attr($post, 'type'),
            'name' => $envServ->getAppEnv('APP_NAME'),
            'env' => $envServ->getAppEnv(),
            'dir' => $envServ->getAppDir(),
            'cdn_host' => $envServ->getAppEnv('CDN_ORIGIN'),
            'sfm_dir_name' => $sfmDirname,
            'is_bo' => $urlServ->isBackoffice()
        ];
        
        return $jsonify ? new Markup(json_encode($appConfig), 'UTF-8') : $appConfig;
    }
    
    public function getImage(string $alt = '', string $src = null, $width = null, $height = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true)
    {
        $src = $this->resizeImage($src, $width, $height, $mode, $backgroundColor);
        $style = $lazy ? 'style="width:auto;height:'.$height.'px"' : '';
        $img = '<img
                    '.$style.'
                    alt="'.$alt.'"
                    width="'.$width.'"
                    height="'.$height.'"
                    src="'.($lazy ? $this->please->serve('env')->getAppEnv('LAZY_LOAD_IMAGES') : $src).'"
                    data-lazy-src="'.$src.'"
                    class="'.$classNames.'"
                >';
        return new Markup($img, 'UTF-8');
    }
    
    public function resizeImage(string $src, $width = null, $height = null, $mode, $backgroundColor = [255, 255, 255])
    {
        return $this->please->serve('image')->resize($src, $width, $height, $mode, $backgroundColor);
    }
    
    public function getPicture(string $alt = '', string $src, array $mediumSizes, array $querySizes = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true)
    {
        $pic = '<picture>';
                if($querySizes) {
                    foreach($querySizes as $maxW => $imgSizes){
                        $w = $imgSizes[0];
                        $h = $imgSizes[1];
                        $src = $this->resizeImage($src, $w, $h, $mode);
                        $style = $lazy ? 'style="width:'.$w.'px;height:'.$h.'px"' : '';
                        $pic .= '<source
                                    '.$style.'
                                    width="'.$w.'"
                                    height="'.$h.'"
                                    class="'.$classNames.'"
                                    srcset="'.$src.'"
                                    media="(max-width:'.$maxW.'px)"
                                >';
                    }
                }
            $w = $mediumSizes[0];
            $h = $mediumSizes[1];
            $src = $this->resizeImage($src, $w, $h, $mode);
            $pic .= '<img
                        alt="'.$alt.'"
                        width="'.$w.'"
                        height="'.$h.'"
                        srcset="'.$src.'"
                        class="'.$classNames.'"
                    >';
        $pic .= '</picture>';

        return new Markup($pic, 'UTF-8');
    }

    private function getMixedAssets($paths, $extension, $isCDN=null)
    {
        $enServ = $this->please->serve("env");
        $dirServ = $this->please->serve("dir");
        $urlServ = $this->please->serve("url");
            
        if( !isset($this->minifier) ){
            $this->minifier = new __PhpHtmlCssJsMinifierService();
        }

        $cssContents = $jsContents = '';
        $mixedKey = md5(json_encode($paths));
        $mixedCssFilepath = $dirServ->dirPath("public/assets/css-js/$mixedKey.min.css");
        $mixedJsFilepath = $dirServ->dirPath("public/assets/css-js/$mixedKey.min.js");

        foreach($paths as $i => $path){
            $v = strpos($path, '|v') !== false ? '?v='.rand(0, 999) : '';
            $path = str_replace('|v', '', $path);
            $id = str_ireplace(['.css','.js','.','/', '-'], ['_','_','_','_','_'], $path);
            if( stripos($path, 'http') === false ){
                $path = $isCDN ? $this->getCDN($path.'.'.$extension) : $dirServ->dirPath("public/assets/$extension/". $path.'.'.$extension);
            }

            $limit = sizeof($paths)-1;
            
            switch($extension){
                case 'css':
                        if( !file_exists($mixedCssFilepath) ){ $cssContents .= file_get_contents($path) . PHP_EOL; }
                        if( $i == $limit ){
                            if(!file_exists($mixedCssFilepath)){
                                
                                $content = str_ireplace('CDN_ORIGIN', $enServ->getAppEnv('CDN_ORIGIN'), $this->minifier->getMinifiedCss($cssContents));
                                $content = str_ireplace('APP_ORIGIN', $enServ->getAppEnv('APP_ORIGIN') . '/' . $enServ->getAppEnv('APP_PREFIX') , $content);
                                
                                file_put_contents($mixedCssFilepath, $content, FILE_APPEND | LOCK_EX);
                            }
                            $cssOutput = '<style data-links='.json_encode($paths).'>'.file_get_contents($dirServ->dirPath("public/assets/css-js/$mixedKey.min.css")).'</style>'."\n";
                            return new Markup($cssOutput, 'UTF-8');
                        }
                    break;
                case 'js' : 
                        if( !file_exists($mixedJsFilepath) ){ $jsContents .= file_get_contents($path) . PHP_EOL; }
                        if( $i == $limit ){
                            if(!file_exists($mixedJsFilepath)){
                                file_put_contents($mixedJsFilepath, $jsContents, FILE_APPEND | LOCK_EX);
                            }
                            //
                            $jsOutput = '<script defer="true" data-links='.json_encode($paths).' src="'.$urlServ->getUrl("assets/css-js/$mixedKey.min.js?v=".rand()).'"></script>'."\n";
                            return new Markup($jsOutput, 'UTF-8');
                        }
                    break;
                default : break;
            }
        }
        return null;
    }
}
