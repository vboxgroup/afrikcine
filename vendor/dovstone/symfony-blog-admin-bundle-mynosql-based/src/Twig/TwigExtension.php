<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Twig;

use Symfony\Component\Filesystem\Filesystem;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Twig\Extension\AbstractExtension;
use ScssPhp\ScssPhp\Compiler;
use Twig\TwigFunction;
use Twig\Markup;

class TwigExtension extends AbstractExtension
{
    public function __construct( PleaseService $please )
    {
        $this->please = $please;
        $this->container = $this->please->getContainer();
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('dd', array($this, 'dd')),
            new TwigFunction('_dump', array($this, '_dump')),
            new TwigFunction('convertToKnpPaginatorBundle', array($this, 'convertToKnpPaginatorBundle')),
            new TwigFunction('mimicKnpPaginator', array($this, 'mimicKnpPaginator')),
            new TwigFunction('getPaginationOffset', array($this, 'getPaginationOffset')),
            new TwigFunction('jasonPaginator', array($this, 'jasonPaginator')),

            new TwigFunction('getTableHeadCssLabel', array($this, 'getTableHeadCssLabel')),

            new TwigFunction('getAppEnv', array($this, 'getAppEnv')),
            new TwigFunction('getUrl', array($this, 'getUrl')),
            new TwigFunction('getPostHref', array($this, 'getPostHref')),
            new TwigFunction('getCurrentUrl', array($this, 'getCurrentUrl')),
            new TwigFunction('getCurrentUrlParamsLess', array($this, 'getCurrentUrlParamsLess')),

            new TwigFunction('preloader', array($this, 'preloader')),
            new TwigFunction('getCDN', array($this, 'getCDN')),
            new TwigFunction('getAsset', array($this, 'getAsset')),
            new TwigFunction('gfWrapper', array($this, 'gfWrapper')),
            new TwigFunction('flash', array($this, 'flash')),
            new TwigFunction('outputError', array($this, 'outputError')),
            new TwigFunction('getAppCss', array($this, 'getAppCss')),
            new TwigFunction('getLess', array($this, 'getLess')),
            new TwigFunction('getHeadAssets', array($this, 'getHeadAssets')),
            new TwigFunction('getThemeAssets', array($this, 'getThemeAssets')),
            new TwigFunction('getLogoSrc', array($this, 'getLogoSrc')),
            new TwigFunction('getImage', array($this, 'getImage')),
            new TwigFunction('image', array($this, 'image')),
            new TwigFunction('getPicture', array($this, 'getPicture')),
            new TwigFunction('picture', array($this, 'picture')),
            new TwigFunction('pic', array($this, 'pic')),

            new TwigFunction('getPost', array($this, 'getPost')),
            new TwigFunction('getAppConfig', array($this, 'getAppConfig')),

            new TwigFunction('acf', array($this, 'acf')),
            new TwigFunction('attr', array($this, 'attr')),
            new TwigFunction('findAcf', array($this, 'findAcf')),
            new TwigFunction('findBy', array($this, 'findBy')),
            new TwigFunction('countBy', array($this, 'countBy')),
            new TwigFunction('minMax', array($this, 'minMax')),
            new TwigFunction('getAttr', array($this, 'getAttr')),
            new TwigFunction('u', array($this, 'u')),
            new TwigFunction('b', array($this, 'b')),

            new TwigFunction('getKnpTplPath', array($this, 'getKnpTplPath')),
            new TwigFunction('getBundleEmptyListView', array($this, 'getBundleEmptyListView')),

            new TwigFunction('getFrenchDate', array($this, 'getFrenchDate')),
            new TwigFunction('getMonth', array($this, 'getMonth')),
            new TwigFunction('getSlug', array($this, 'getSlug')),

            new TwigFunction('p', array($this, 'p')),
            new TwigFunction('s', array($this, 's')),
            new TwigFunction('c', array($this, 'c')),
            new TwigFunction('l', array($this, 'l')),
            new TwigFunction('getBody', array($this, 'getBody')),
            new TwigFunction('getPartial', array($this, 'getPartial')),
            new TwigFunction('getCard', array($this, 'getCard')),
            new TwigFunction('getLayout', array($this, 'getLayout')),
            new TwigFunction('comp', array($this, 'comp')),
            new TwigFunction('getComponent', array($this, 'getComponent')),
            new TwigFunction('sc', array($this, 'sc')),
            new TwigFunction('getShortcode', array($this, 'getShortcode')),
            new TwigFunction('getSection', array($this, 'getSection')),
            new TwigFunction('getSections', array($this, 'getSections')),
            new TwigFunction('card', array($this, 'card')),
            new TwigFunction('partial', array($this, 'partial')),
            new TwigFunction('section', array($this, 'section')),
            new TwigFunction('xhrRendering', array($this, 'xhrRendering')),
            new TwigFunction('xhr', array($this, 'xhr')),
            new TwigFunction('skeleton', array($this, 'skeleton')),
            new TwigFunction('skel', array($this, 'skel')),
            new TwigFunction('tpl', array($this, 'tpl')),
            new TwigFunction('compress', array($this, 'compress')),
            new TwigFunction('getNav', array($this, 'getNav')),
            new TwigFunction('getBreadcrumb', array($this, 'getBreadcrumb')),
            new TwigFunction('orderBy', array($this, 'orderBy')),

            //
            new TwigFunction('getCached', array($this, 'getCached')),
            new TwigFunction('setStorage', array($this, 'setStorage')),
            new TwigFunction('getStorage', array($this, 'getStorage')),
            new TwigFunction('getCurl', array($this, 'getCurl')),
            new TwigFunction('setGlobal', array($this, 'setGlobal')),
            new TwigFunction('getGlobal', array($this, 'getGlobal')),
            new TwigFunction('unsetGlobal', array($this, 'unsetGlobal')),

            new TwigFunction('getDateTime', array($this, 'getDateTime')),
            new TwigFunction('getTimeAgo', array($this, 'getTimeAgo')),
            new TwigFunction('getDaysOptions', array($this, 'getDaysOptions')),
            new TwigFunction('getMonthsOptions', array($this, 'getMonthsOptions')),
            new TwigFunction('getYearsOptions', array($this, 'getYearsOptions')),
            new TwigFunction('ellipsisDate', array($this, 'ellipsisDate')),
            new TwigFunction('ellipsisText', array($this, 'ellipsisText')),
            new TwigFunction('indexify', array($this, 'indexify')),

            new TwigFunction('getUserFullName', array($this, 'getUserFullName')),
            new TwigFunction('getUserRole', array($this, 'getUserRole')),

            new TwigFunction('isHome', array($this, 'isHome')),
            new TwigFunction('isActive', array($this, 'isActive')),
            new TwigFunction('isBot', array($this, 'isBot')),
            new TwigFunction('isXHR', array($this, 'isXHR')),
            new TwigFunction('isNotXHR', array($this, 'isNotXHR')),
            new TwigFunction('gTag', array($this, 'gTag')),
            new TwigFunction('beginHTML', array($this, 'beginHTML')),
            new TwigFunction('endHTML', array($this, 'endHTML')),

            new TwigFunction('userCan', array($this, 'userCan')),
            new TwigFunction('userIs', array($this, 'userIs')),

            new TwigFunction('fileWeightFormatShort', array($this, 'fileWeightFormatShort')),
            new TwigFunction('priceFormat', array($this, 'priceFormat')),
            new TwigFunction('randomify', array($this, 'randomify')),
            new TwigFunction('getColorsGrid', array($this, 'getColorsGrid')),
            new TwigFunction('i', array($this, 'i')),
            new TwigFunction('routeActiveClass', array($this, 'routeActiveClass')),
            new TwigFunction('getCountriesOptions', array($this, 'getCountriesOptions')),
            new TwigFunction('getCountriesCallCode', array($this, 'getCountriesCallCode')),
            new TwigFunction('buildTree', array($this, 'buildTree')),

            new TwigFunction('getQueryRedir', array($this, 'getQueryRedir')),
            new TwigFunction('uId', array($this, 'uId')),
            new TwigFunction('uId8', array($this, 'uId8')),
        );
    }

    public function getAppEnv($var = 'APP_ENV')
    {
        return $this->please->serve('env')->getAppEnv($var);
    }

    public function getUrl($path = '/')
    {
        return $this->please->serve('url')->getUrl($path);
    }

    public function getCurrentUrl()
    {
        return $this->please->serve('url')->getCurrentUrl();
    }

    public function getCurrentUrlParamsLess()
    {
        return $this->please->serve('url')->getCurrentUrlParamsLess();
    }

    public function getPostHref($post)
    {
        return $this->please->serve('url')->getPostHref($post);
    }

    public function preloader($v=1, $className=null)
    {
        $hiddenClass = $this->please->isXHR() ? 'hidden' : '';

        return new Markup('<div class="app-preloader-wrapper '.$className . $hiddenClass .'">
            <div>
                <div class="logo"><img class="logo" src="'. $this->getLogoSrc($v) . '"></div>
                <img src="'. $this->please->serve('asset')->getCDN('swagg/assets/img/loading-spinner.gif') .'">
            </div>
        </div>', 'UTF-8');
    }

    public function getCDN($path='/', $extension='css')
    {
        return new Markup($this->please->serve('asset')->getCDN($path, $extension), 'UTF-8');
    }

    public function getHeadAssets($arr = [], $reset=false)
    {
        return new Markup($this->please->serve('asset')->getHeadAssets( $arr, $reset ), 'UTF-8');
    }

    public function getAsset($asset, $attr='')
    {
        return $this->please->serve('asset')->getAsset($asset, $attr='');
    }

    public function getThemeAssets($assets, $extension='css')
    {
        return $this->please->serve('asset')->getThemeAssets($assets, $extension);
    }

    public function getLogoSrc($v=1): string
    {
        return $this->please->serve('asset')->getLogoSrc($v);
    }

    public function getImage(string $alt = '', string $src = null, $width = null, $height = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true): string
    {
        return $this->please->serve('asset')->getImage($alt, $src, $width, $height, $mode, $classNames, $backgroundColor, $lazy);
    }

    public function image(string $alt = '', string $src = null, $width = null, $height = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true): string
    {
        return $this->please->serve('asset')->getImage($alt, $src, $width, $height, $mode, $classNames, $backgroundColor, $lazy);
    }

    public function getPicture(string $alt = '', string $src = null, array $mediumSizes, array $querySizes = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true): string
    {
        return $this->please->serve('asset')->getPicture($alt, $src, $mediumSizes, $querySizes, $mode, $classNames, $backgroundColor, $lazy);
    }

    public function picture(string $alt = '', string $src = null, array $mediumSizes, array $querySizes = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true): string
    {
        return $this->please->serve('asset')->getPicture($alt, $src, $mediumSizes, $querySizes, $mode, $classNames, $backgroundColor, $lazy);
    }

    public function pic(string $alt = '', string $src = null, array $mediumSizes, array $querySizes = null, $mode='r', $classNames='', $backgroundColor = [255, 255, 255], $lazy = true): string
    {
        return $this->please->serve('asset')->getPicture($alt, $src, $mediumSizes, $querySizes, $mode, $classNames, $backgroundColor, $lazy);
    }

    public function gfWrapper($label, $name, $value='', $attr=''): string
    {
        $value = htmlentities($value);
        $cls = strpos($attr, 'class') !== false ? preg_replace('/(.*)(class=")(.*)(")(.*)/m', '$3', $attr) : '';
        return new Markup("<div class=\"gf-wrapper\">
                <input class=\"gf-control form-control ".($value!==''?'gf-full':'')." $cls\" name=\"$name\" $attr value=\"$value\">
                <div class=\"gf-label\">$label</div>
            </div>", 'UTF-8');
    }

    public function flash(string $flashKey, string $name, string $className = ''): string
    {
        $flashes = $this->please->getGlobal($flashKey);
        if(is_string($flashes)){
            $message = $flashes;
            $className = $name;
            $this->please->unsetGlobal($flashKey);
        }
        elseif( isset($flashes[$name]) ){
            $message = $flashes[$name];
            unset($flashes[$name]);
            $this->please->setFlash([ $flashKey => $flashes]);
        }
        if(isset($message)){
            return new Markup('<div class="'.$className.'">'.$message.'</div>', 'UTF-8');
        }
        return '';
    }

    public function getAppCss($reset = false)
    {
        return $this->please->serve('asset')->getAppCss($reset);
    }
    
    public function getLess(array $files = [])
    {
        return $this->please->serve('asset')->getLess($files);
    }
    
    public function getAppConfig($jsonify=true)
    {
        
        return $this->please->serve('asset')->getAppConfig($jsonify);
    }
    
    public function getPost()
    {
        $post = $this->please->getGlobal("post");

        if(!isset($post['fullTitle'])){
            $APP_NAME = $this->please->serve('env')->getAppEnv('APP_NAME');
            $post['fullTitle'] = ($post['title'] ?? $post['secondTitle'] ?? ''). ' | ' .$APP_NAME;
            $this->please->setGlobal(["post" => $post]);
        }
        return $this->please->getGlobal("post");
    }
    
    public function findAcf(string $type, int $limit = -1, array $orderBy = ['createdAt' => 'desc'], int $offset = 0)
    {   
        return $this->please->getRepo('bloggy')->findAcf($type, $limit, $orderBy, $offset);
    }

    public function findBy(string $repository='bloggy', array $criteria, array $orderBy = ['createdAt' => 'desc'], int $limit = 15, $offset = null)
    {   
        if( in_array($repository, ['B', 'b', 'Bloggy', 'bloggy']) ){
            $repository = 'bloggies';
        }
        if( in_array($repository, ['U', 'u', 'User', 'user']) ){
            $repository = 'users';
        }
        $rows = $this->please->getMyNoSQLCollection($repository)->findBy($criteria)->orderBy($orderBy)->limit($limit)->offset($offset)->fetch();
        return $rows && isset($rows[0]) && $limit==1 ? $rows[0] : $rows;
    }
    
    public function countBy(string $repo='b', array $criteria=[])
    {
        return $repo()->countBy($criteria)->fetch();
    }
    
    public function minMax(string $repo='b', string $prop='', array $criteria=[])
    {
        return $repo()->minMax($prop, $criteria);
    }
    
    public function acf(?array $data, string $fieldsPath, $onNull=null, $isEmptiale=true)
    {   
        return $this->please->serve('acf')->getAcf($data, $fieldsPath, $onNull, $isEmptiale);
    }
    
    public function getAttr($data, $attributePath, $onNull=null, $isEmptiale=true)
    {   
        return attr($data, $attributePath, $onNull, $isEmptiale);
    }
    
    public function attr($data, $fieldsPath, $onNull=null, $isEmptiale=true)
    {   
        return attr($data, $fieldsPath, $onNull, $isEmptiale);
    }
    
    public function u()
    {   
        return u();
    }
    
    public function b()
    {   
        return b();
    }
    
    public function getKnpTplPath()
    {   
        return '@DovStoneSymfonyBlogAdminBundleMyNoSQLBased/partials/twitter_bootstrap_v4_pagination.html.twig';
    }
    
    public function getBundleEmptyListView(string $message = 'Le dossier est vide.', string $icon='ban')
    {
        return new Markup($this->please->serve('template')->getBundleEmptyListView($message, $icon), 'UTF-8');
    }
    
    public function getFrenchDate($dateTime=null, string $format = "D/d/M/Y H:i:s")
    {
        return $this->please->serve('time')->getFrenchDate($dateTime, $format);
    }
    
    public function getMonth($dateTime = null, $type = null, $months_prefixed = null, $ellipsis = null)
    {
        return $this->please->serve('time')->getMonth($dateTime, $type, $months_prefixed, $ellipsis);
    }
    
    public function getSlug(string $string, $replacement='-', $lowercase=true)
    {
        return $this->please->serve('string')->getSlug($string, $replacement, $lowercase);
    }
    
    public function getSections(array $sections = [])
    {
        $view = '';
        foreach ($sections as $section) {
            $view .= $this->getSection($section[0], $section[1] ?? []);
        }
        return $view;
    }

    public function getSection($section, array $params = [])
    {
        return new Markup('<div sg-section="'.$section.'">' . $this->container->get('twig')->render("sections/$section.html.twig", $params) . '</div>', 'UTF-8');
    }

    public function section($section, array $params = [])
    {
        return $this->getSection($section, $params);
    }

    public function s($section, array $params = [])
    {
        return $this->getSection($section, $params);
    }

    public function getPartial($partial, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("partials/$partial.html.twig", $params), 'UTF-8');
    }

    public function partial($partial, array $params = [])
    {
        return $this->getPartial($partial, $params);
    }

    public function p($partial, array $params = [])
    {
        return $this->getPartial($partial, $params);
    }

    public function getCard($card, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("cards/$card.html.twig", $params), 'UTF-8');
    }

    public function card($card, array $params = [])
    {
        return $this->getCard($card, $params);
    }

    public function c($card, array $params = [])
    {
        return $this->getCard($card, $params);
    }

    public function getLayout($filename, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("layouts/$filename.html.twig", $params), 'UTF-8');
    }

    public function l($filename, array $params = [])
    {
        return $this->getLayout($filename, $params);
    }

    public function getComponent($filename, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("components/$filename.html.twig", $params), 'UTF-8');
    }

    public function comp($filename, array $params = [])
    {
        return $this->getComponent($filename, $params);
    }

    public function getShortcode($filename, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("shortcodes/$filename.html.twig", $params), 'UTF-8');
    }

    public function sc($filename, array $params = [])
    {
        return $this->getShortcode($filename, $params);
    }

    public function xhrRendering($filename, array $params = [])
    {
        if( isset($params['noXHR']) && $params['noXHR'] === true){
            return new Markup($this->container->get('twig')->render("xhr-rendering/$filename.html.twig", $params), 'UTF-8');
        }
        return $this->please->serve('xhr')->rendering($filename, $params);
    }

    public function xhr($filename, array $params = [])
    {
        return $this->xhrRendering($filename, $params);
    }

    public function skeleton($filename, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("skeletons/$filename.html.twig", $params), 'UTF-8');
    }

    public function skel($filename, array $params = [])
    {
        return $this->skeleton($filename, $params);
    }

    public function tpl($filename, array $params = [])
    {
        return new Markup($this->container->get('twig')->render("tpl/$filename.html.twig", $params), 'UTF-8');
    }

    public function compress($content)
    {
        return $this->please->compress($content);
    }

    public function getBody($default=null, $persistHtml = false)
    {
        $post = $this->please->getGlobal('post');
        $html = $post['html'] ?? '';
        return new Markup('<div id="swagg_main" sg-persist-html="'.( $persistHtml ? 'true':'false' ).'">'. ( $html && $html !== '' ? $html : $default ?? '<div sg-empty></div>' ).'</div>', 'UTF-8');
    }

    public function getNav($params = [])
    {
        return new Markup($this->please->serve('nav')->getNav($params), 'UTF-8');
    }

    public function getBreadcrumb($params = [])
    {
        return new Markup($this->please->serve('nav')->getBreadcrumb($params), 'UTF-8');
    }

    public function orderBy($items = [], $field)
    {
        return $this->please->serve('nav')->orderBy($items, $field);
    }

    public function path($path, array $params = [])
    {
        return $this->please->serve('url')->getPath($path, $params);
    }
    
    public function setStorage($bigData, $bundleStorage=null, $sessionIdRelated = null)
    {
        return $this->please->setStorage($bigData, $bundleStorage, $sessionIdRelated);
    }

    public function getStorage($fileName, $bundleStorage=null)
    {
        return $this->please->getStorage($fileName, $bundleStorage);
    }

    public function getCurl($url, $isRoute = true)
    {
        return $this->please->getCurl($url, $isRoute);
    }

    public function setGlobal($bigData)
    {
        return $this->please->setGlobal($bigData);
    }

    public function getGlobal($globalName, $default = null)
    {
        return $this->please->getGlobal($globalName, $default);
    }

    public function unsetGlobal($globalName)
    {
        return $this->please->unsetGlobal($globalName);
    }

    public function getCached($path)
    {
        return $this->please->getCached($path);
    }
    
    public function getDateTime($datetime = null)
    {
        return $this->please->serve('time')->getDateTime($datetime);
    }
    
    public function getTimeAgo($datetime = null)
    {
        return $this->please->serve('time')->getTimeAgo($datetime);
    }
    
    public function getDaysOptions($label = 'Jour', $selected = null, $required=true)
    {
        return new Markup($this->please->serve('time')->getDaysOptions($label, $selected, $required), 'UTF-8');
    }
    
    public function getMonthsOptions($label = 'Mois', $selected=null, $required=true, $short = null)
    {
        return new Markup($this->please->serve('time')->getMonthsOptions($label, $selected, $required, $short), 'UTF-8');
    }
    
    public function getYearsOptions($label = 'Années', $selected=null, $required=true, $from = 1950, $to=null, $order='asc')
    {
        return new Markup($this->please->serve('time')->getYearsOptions($label, $selected, $required, $from, $to, $order), 'UTF-8');
    }
    
    public function ellipsisDate($datetime = null)
    {
        return $this->please->serve('time')->ellipsisDate($datetime);
    }
    
    public function ellipsisText(string $string, int $max = 100, string $append = '...')
    {
        return $this->please->serve('string')->ellipsisText($string, $max, $append);
    }
    
    public function indexify($zeroIndexedKey, $limit)
    {
        $page = $this->please->getRequestStackQuery()->get('page', 1);
        return (($page * $limit + $zeroIndexedKey) - 1) - $limit + 2;
    }
    
    public function getUserRole($user = null)
    {
        return $this->please->serve('security')->getUserRole($user);
    }
    
    public function getUserFullName($user = null)
    {
        return $this->please->serve('user')->getUserFullName($user);
        /*$user = $user ?? $this->please->getUser() ?? null;
        if($user){
            return attr($user, 'lastname').' '.attr($user, 'firstname');
        }
        return null;*/
    }
    
    public function isHome()
    {
        return $this->please->serve('dir')->isHome();
    }
    
    public function priceFormat($number, int $decimals=0, string $dec_point='', string $thousands_sep='.')
    {
        return $this->please->serve('string')->priceFormat($number, $decimals, $dec_point, $thousands_sep);
    }
    
    public function isActive($post)
    {
        return $this->please->serve('nav')->isActive($post);
    }
    
    public function routeActiveClass($routeName, $activeClass='active')
    {
        return $this->please->serve('nav')->routeActiveClass($routeName, $activeClass);
    }
    
    public function getCountriesOptions($selected="Côte d'Ivoire", $countries = null)
    {
        return $this->please->serve('mix')->getCountriesOptions($selected, $countries);
    }
    
    public function getCountriesCallCode($selected=225, $countries = null)
    {
        return $this->please->serve('mix')->getCountriesCallCode($selected, $countries);
    }
    
    public function buildTree($collection, $renderType='option', $preventId=null, $params=[])
    {
        return $this->please->serve('dir')->buildTree($collection, $renderType, $preventId, $params);
    }
    
    public function getQueryRedir()
    {
        return $this->please->serve('url')->getQueryRedir();
    }
    
    public function uId()
    {
        return $this->please->serve('string')->uId();
    }
    
    public function uId8()
    {
        return $this->please->serve('string')->uId8();
    }
    
    public function isBot()
    {
        return $this->please->isBot();
    }
    
    public function isXHR()
    {
        return $this->please->isXHR();
    }
    
    public function isNotXHR()
    {
        return !$this->please->isXHR();
    }
    
    public function beginHTML($params = [])
    {
        return $this->please->serve('template')->beginHTML($params);
    }
    
    public function endHTML($params = [])
    {
        return $this->please->serve('template')->endHTML($params);
    }
    
    public function userCan($role = 'admin', $strictRole = true, $user = null)
    {
        return $this->please->serve('security')->userCan($role, $strictRole, $user);
    }
    
    public function userIs($role = 'admin', $strictRole = true, $user = null)
    {
        return $this->please->serve('security')->userCan($role, $strictRole, $user);
    }
    
    public function fileWeightFormatShort($n)
    {
        return $this->please->serve('string')->fileWeightFormatShort($n);
    }
    
    public function randomify($list, $limit=null)
    {
        return $this->please->serve('mix')->randomify($list, $limit);
    }

    public function getColorsGrid()
    {
        return [
            ['#ff281b', '#ffb88e'],
            ['#89b85a', '#005048'],
            ['#98f0e2', ''],
            ['#b49ac9', '#4a2964'],
            ['#925a9f', '#e7e0ec'],
            ['#f1af21', '#005804'],
            ['#f137a6', '#ffeb3b'],
            ['#509bf6', '#e9ceff']
        ];
    }

    public function i($code, $classNames='')
    {
        return new Markup('<span class="iconify '.$classNames.'" data-icon="'.$code.'" data-inline="false"></span>', 'UTF-8');
    }
    
    public function gTag($id)
    {
        return $this->please->serve('template')->gTag($id);
    }
    
    public function convertToKnpPaginatorBundle($items = [], $perPage=15)
    {
        return $this->please->convertToKnpPaginatorBundle($items, $perPage);
    }
    
    public function jasonPaginator($items = [], $perPage=15)
    {
        return $this->please->jasonPaginator($items, $perPage);
    }
    
    public function mimicKnpPaginator(int $total, $perPage=15)
    {
        return $this->please->mimicKnpPaginator($total, $perPage);
    }
    
    public function getPaginationOffset(int $limit, string $pageQuery = 'page')
    {
        return $this->please->getPaginationOffset($limit, $pageQuery);
    }
    
    public function getTableHeadCssLabel($params = [])
    {
        $params = (object) array_merge([
            'labels' => [],
            'maxWidth' => 992,
            'selector' => 'table',
            'labelWidth' => 90,
            'labelAutoLineHeight' => null,
            'height' => null,
            'minHeight' => 65,
            'display' => 'block',
            'evenBgColor' => 'transparent',
            'labelCssRules' => null
        ], $params);

        $params->height = $params->height ? ";height:{$params->height}px;":"";
        $labelCssRules = $params->labelCssRules ? $params->selector." td::before{{$params->labelCssRules}}":"";

        $css = "<style>
        @media screen and (max-width:{$params->maxWidth}px){
            $params->selector thead{display:none}
            $params->selector tbody td{display:$params->display;$params->height;position:relative;align-items:center}    
            $params->selector tbody td:nth-child(2n+1){background-color:$params->evenBgColor}
            $labelCssRules
        ";
        foreach($params->labels as $i => $label){
            ++$i;
            $labelAutoLineHeight = $params->labelAutoLineHeight ? ";line-height:".($params->minHeight-17)."px;":"";
            $css .= $params->selector.' td:nth-of-type('. $i .')::before{content:"'.$label.'";width:'.$params->labelWidth.'px'.$labelAutoLineHeight.';display:table;text-align:left}';
        }
        $css .= '}</style>';
        
        return new Markup($css, 'UTF-8');
    }

    public function dd($data) { dd($data); }

    public function _dump($data) { dump($data); }
}
