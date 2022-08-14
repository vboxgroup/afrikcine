<?php
namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__PhpHtmlCssJsMinifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use ScssPhp\ScssPhp\Compiler;
use Twig\Markup;

class TemplateService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function sanitizeViewLinks($view)
    {
        $envServ = $this->please->serve('env');
        
        //
        $oldHosts = $replacements = [];
        $old_hosts = $envServ->getAppEnv('APP_OLD_ORIGINS');
        if( !empty($old_hosts) ){
            $baseUrl = $this->please->serve('url')->getBaseUrl();
            $old_hosts = explode('|', $old_hosts);
            foreach ($old_hosts as $old_host) { 

                $old_host = $old_host . '/';
                $lastChar = $baseUrl[strlen($baseUrl)-1];
                $baseUrl = $baseUrl . ($lastChar == '/' ? '' : '/');

                $oldHosts[] = $old_host;
                $replacements[] = $baseUrl;

                    // any json version
                    $oldHosts[] = trim(json_encode($old_host), '"');
                    $replacements[] = trim(json_encode($baseUrl), '"');
            }
        }
        return str_ireplace($oldHosts, $replacements, $view);
    }

    public function sanitizeView($view)
    {
        $envServ = $this->please->serve('env');

        $view = $this->sanitizeViewLinks($view);

        if( ($LAZY_LOAD_IMAGES = $envServ->getAppEnv('LAZY_LOAD_IMAGES')) != '' ){

            $placeholder = $this->please->serve('url')->getUrl($LAZY_LOAD_IMAGES);

            preg_match_all('/(<main)([\s\S]+)(<\/main>)/U', $view, $matches);

            if($matches){

                /*$view = preg_replace_callback('/(<main)([\s\S]+)(<\/main>)/U', function($m) use ($placeholder) {
                    //return str_ireplace('<img src', '<img src="'.$placeholder.'" data-lazy-src', $m[0]);
                    $re = '/(<img)(.+)(src)(.+)(>)/m';
                    $str = $m[0];
                    $subst = '$1$2src="'.$placeholder.'" data-lazy-$3$4$5';
                }, $view);*/

                $view = preg_replace_callback('/(style=\"background-image:)(.+)(\")/U', function($m) use ($placeholder) {
                    //$src = preg_replace_callback('/(url\()([\s\S]+)(\))/U', function($m){
                    //  return $m[2];
                    //}, trim($m[2]));
                    return 'style="background:url('.$placeholder.') center center / contain no-repeat!important" data-lazy-style="background-image:'.trim($m[2]).'"';
                }, $view);
            }
        }

        if ((new Filesystem())->exists($this->please->serve('dir')->getProjectDir() . '/src/Service/ExecuteBeforeService.php')) {
            if (method_exists(\App\Service\ExecuteBeforeService::class, '__hookView') && $this->please->prevContainer->has('service.execute_before')) {
                $hookView = $this->please->prevContainer->get('service.execute_before')->__hookView($view);
                if( !empty($hookView) ){
                    $view = $hookView;
                }
            }
        }

        $view = str_replace(["\r", "\n"], '', $view); // removing new lines
        //$view = preg_replace('/\s+/', ' ', $view);
        $view = preg_replace('~>\s+<~', '><', $view); // removing whitespaces
        $view = preg_replace('/( )(\/\/)( )/m', ';', $view);
        $view = preg_replace('/(\'use strict\')/m', "'use strict';", $view);
        $view = str_replace("'use strict';;", "'use strict';", $view);

        $view = str_ireplace('</body>', "<script>AddScript(function(){\"use strict\";LazyLoading.init();})</script></body>", $view);

        return new Markup($view ?: "<h1 style='background-color:red;text-align:center;color:#fff;padding:15px;border-radius:8px'>La vue doit \"sortir\" au moins un caractère.</h1>", 'UTF-8');
    }

    public function getBundleEmptyListView($message = 'Le dossier est vide.', $icon='ban')
    {
        return $this->renderView('@DovStoneSymfonyBlogAdminBundleMyNoSQLBased/partials/empty-list.html.twig', compact('message', 'icon'));
    }

    public function getTpl($tpl, array $parameters=[])
    {  
        $view = $this->renderView($tpl.'.html.twig', $parameters);
        $tplServ = $this->please->serve('template');
        //$view = $this->please->serve('url')->isBackoffice() ? $view : $tplServ->sanitizeView($view);
        //
        if( $this->please->isXHR() && ($this->please->getRequestStackQuery()->get('_ajaxify') || $this->please->getRequestStackRequest()->get('_ajaxify')) ){
            $tplServ = $this->please->serve('template');
            $response = new JsonResponse(
                $tplServ->parseView($view)
            );
        }
        else {
            $response = new Response($view);
        }
        //
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        //
        return $response;
    }

    public function parseView($view)
    {
        preg_match('/(<title>)([\s\S]+)(<\/title>)/U', $view, $title); if($title){ $title = $title[2]; }
        if( !$title ){ preg_match('/(data-meta-title=\")([\s\S]+)(\")/U', $view, $title); if($title){ $title = $title[2]; } }
        preg_match('/(<body)([\s\S]+)(<\/body>)/U', $view, $body); if($body){ $body = $body[0]; }

        return [
            'title' => str_replace(' | ', ' • ', $title  ?? 'Une erreur interne est survenue'),
            'body' => $body ?: $view,
            'jsonifiedView' =>  true
        ];
    }

    public function beginHTML($params)
    {
        $this->beginHTMLparams = $params;
        
        $envServ = $this->please->serve('env');
        $assetServ = $this->please->serve('asset');

        $APP_NAME = $this->please->serve('env')->getAppEnv('APP_NAME');
        $post = $this->please->getGlobal('post');
        $title = ($post['secondTitle'] ?? $post['title'] ?? ''). ' • ' .$APP_NAME;
        $image = $post['image'] ?? $this->please->serve('asset')->getLogoSrc();
        $keywords = $post['keywords'] ?? $this->please->serve('string')->getTag($APP_NAME);
        $description = strip_tags(($post['description'] ?? ''));
        $currentUrl = $this->please->serve('url')->getCurrentUrl();
        $favicon = $this->please->serve('url')->getUrl('favicon.png?v=' . rand(0, 999));

        if( isset($params['gTag']) ){ $gTag = '<script async src="https://www.googletagmanager.com/gtag/js?id='.$params['gTag'].'"></script>'; }
        if( isset($params['headAssets']) ){ $headAssets = $assetServ->getHeadAssets($params['headAssets'][0], $params['headAssets'][1] ?? false); }
        if( isset($params['themeCssAssets']) ){ $themeCssAssets = $assetServ->getThemeAssets($params['themeCssAssets'], 'css'); }
        if( isset($params['themeJsAssets']) ){ $themeJsAssets = $assetServ->getThemeAssets($params['themeJsAssets'], 'js'); }
        if( isset($params['cssCDN']) ){ $cssCDN = $assetServ->getCDN($params['cssCDN']); }
        if( isset($params['jsCDN']) ){ $jsCDN = $assetServ->getCDN($params['jsCDN'], 'js'); }
        if( isset($params['scrollBarSelector']) ){
            $customSelector = is_string($params['scrollBarSelector'])?','.$params['scrollBarSelector']:',body';
            $color = $params['themeColor'] ?? '#fff';
            $scrollBar = '<style>html::-webkit-scrollbar-track'.$customSelector.'::-webkit-scrollbar-track{-webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);border-radius:0;background-color:#f5f5f5}html::-webkit-scrollbar'.$customSelector.'::-webkit-scrollbar{width:10px;height:12px;background-color:'.$color.'}html::-webkit-scrollbar-thumb'.$customSelector.'::-webkit-scrollbar-thumb{border-radius:0;-webkit-box-shadow:inset 0 0 6px rgba(0,0,0,.3);background-color:'.$color.'}</style>';
        }

$html = '<!DOCTYPE html>
<html lang="fr" data-app-config=\''.$assetServ->getAppConfig().'\'>
<head>
    '.(isset($params['noIndex']) ? '<meta name="robots" content="noindex">':'').'
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>'.$title.'</title>
    <meta name="description" content="'.$description.'">
    <meta name="keywords" content="'.$keywords .', '. $APP_NAME.'">
    <meta name="author" content="Silvère Comlan Dovoui • +255 0 757 337 871 • www.silveredovoui.com">
    <meta name="url" content="'.$currentUrl.'">
    <meta name="theme-color" content="'.($params['themeColor'] ?? '#fff').'" />
    <link rel="canonical" href="'.$currentUrl.'" />
    <link rel="shortcut icon" href="'.$favicon.'" type="image/x-icon">
    <link rel="icon" href="'.$favicon.'" type="image/x-icon">
    <meta property="og:url" content="'.$currentUrl.'" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="'.$title.'" />
    <meta property="og:description" content="'.$description.'" />
    <meta property="og:image" content="'.$image.'" />
    '.($gTag??'').'
    '.($headAssets??'').'
    '.($themeCssAssets??'').'
    '.($themeJsAssets??'').'
    '.($cssCDN??'').'
    '.($jsCDN??'').'
    '.($scrollBar??'').'
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family='.($params['googleFonts'] ?? 'Roboto').'&display=swap" rel="stylesheet">
    <style>.app-preloader-wrapper{position:fixed;top:0;left:0;width:100%;height:100%;text-align:center;background:#fff;z-index:99999999}.app-preloader-wrapper>div{top:50%;position:absolute;transform:translateY(-50%);left:0;right:0;margin:auto}html body::before,html body::after{display:none}body.hide-app-preloader-wrapper .app-preloader-wrapper{display:none}.app-page{position:absolute;top:0;left:0;right:0;bottom:0;height:100%;width:100%}</style>'.(isset($params['preloaderColor']) && $params['preloaderColor']==false?'':$preloader='<style>body:not(.pending) .app-ajax-loader{transform:scale(0)}body.pending .app-ajax-loader{transform:scale(1)}.app-ajax-loader{transition:all .3s ease;position:fixed;left:0;top:0;height:100%;width:100%;z-index:99999999}.app-ajax-loader img{background:#fff;border-radius:100%;position:absolute;top:50%;left:50%;padding:10px;transform:translate(-50%,-50%);box-shadow: 0 0 30px 15px '.$params['preloaderColor'].'}.has-noise{position:relative}.has-noise::after{bottom:0;content:"";left:0;opacity:.5;position:absolute;right:0;top:0;z-index:0}.has-noise::after{background-image:url('.$assetServ->getAsset('pattern-noisy.webp').')}.has-noise.dark{background-color:#1a1a1a}.has-noise.darker{background-color:#000}.has-noise.primary{background-color:#005404}.has-noise *{position:relative;z-index:1}}</style>').'
</head>';
        return new Markup($html, 'UTF-8');
    }

    public function endHTML($params)
    {
        $assetServ = $this->please->serve('asset');
        if( isset($params['themeJsAssets']) ){ $themeJsAssets = $assetServ->getThemeAssets($params['themeJsAssets'], 'js'); }
        if( isset($this->beginHTMLparams['preloaderColor']) && is_string($this->beginHTMLparams['preloaderColor'])){
            $preloader = '<div class="app-ajax-loader"><img src="'.$assetServ->getCDN('swagg/assets/img/loading-spinner.gif').'" alt="Chargement en cours..."></div>';
        }

        $html = ($themeJsAssets??'') . ($preloader??'') . '</body></html>';

        return new Markup($html, 'UTF-8');
    }

    public function gTag($id)
    {
        return new Markup("<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '$id');</script>", 'UTF-8');
    }

    /**
     * call within WebsiteController::render200($post)
     */
    public function hookPost($post)
    {
        if ((new Filesystem())->exists($this->please->serve('dir')->getProjectDir() . '/src/Service/ExecuteBeforeService.php')) {
            if (method_exists(\App\Service\ExecuteBeforeService::class, '__hookPost') && $this->please->prevContainer->has('service.execute_before')) {
                $hooked = $this->please->prevContainer->get('service.execute_before')->__hookPost($post);
                if( $hooked ){
                    return $hooked;
                }
            }
        }
        return $post;
    }

    private function convertAnyScssToCss($view)
    {
        return $view;
        preg_match_all('/(<style type=\")(text\/scss|scss)(\">)([\s\S]+)(<\/style>)/U', $view, $matches);
        if($matches){
            $view = preg_replace_callback('/(<style type=\")(text\/scss|scss)(\">)([\s\S]+)(<\/style>)/U', function($m) {
                return '<style>'.(new Compiler())->compile($m[4]).'</style>'."\n";
            }, $view);
        }
        return $view;
    }
}
