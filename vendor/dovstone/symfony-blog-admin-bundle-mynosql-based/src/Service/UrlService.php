<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__PhpHtmlCssJsMinifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Markup;

class UrlService extends AbstractController
{
    protected $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function getHome()
    {
        return $this->getUrl();
    }

    public function getRouterContext()
    {
        return $this->please->prevContainer->get('router')->getContext();
    }

    public function getRequestStack()
    {
        return $this->please->prevContainer->get('request_stack');
    }

    public function getQueryString(): string
    {
        return $this->getRouterContext()->getQueryString();
    }

    public function getUrl($href = '/'): string
    {
        // Ensuring we can return a proper absolute url weither the given href
        // for exemple even from C:\Apps\Web\sf4\public\uploads\2018\03
        $href = str_ireplace($this->please->prevContainer->get('kernel')->getProjectDir() . '/public', '', $href);

        // Replacing trailing backslash(es) to slash
        $href = preg_replace('~\\\+~', '/', $href);

        // Replacing slash(es) to slash
        //$href = $this->getAppBaseUrl() . '/' . trim(preg_replace('~/+~', '/', $href), '/');
        $href = $this->getBaseUrl() . '/' . trim(preg_replace('~/+~', '/', $href), '/');

        // Returning the absolute url
        return $href;
    }
            
    public function getRelativeUrl($href = '/', $replace = ''): string
    {
        //replacing the host and app dev dir
        $href = str_ireplace($this->getBaseUrl() . $replace, '', $href);
        return $href;
    }

    public function getPath($path, $params=[])
    {
        $symfPath = $this->please->prevContainer->get('router')->generate($path, $params);
        return $this->please->serve('env')->getAppEnv('APP_ORIGIN') . $symfPath;
    }

    public function getUrlsManagedView(string $view)
    {
        return $view;
    }

    public function getCurrentUrl(): string
    {
        $getQueryString = $this->getRouterContext()->getQueryString();
        $qString = (!empty($getQueryString) ? '?' . $getQueryString : '');
        return $this->getHandledUrl() . $qString;
    }

    public function getQueryRedir(): string
    {
        $url = '&_redirect=' . $this->please->getRequestStackQuery()->get('_redirect', $this->getCurrentUrl());
        return $url;
    }

    public function getPostHref($post): string
    {
        if( is_numeric($post) ){
            $post = b()->find($post)->fetch();
        }

        $customHref = attr($post, 'customHref');
        if( !empty($customHref) ){
            return strpos($customHref, 'http') !== false ? $customHref : $this->please->serve('url')->getUrl($customHref);
        }

        $postId = $post['id'];

        $cachePostHref = b()->findOneBy([['type','==','href'],['postId','==',$postId]])->fetch();
        if( $cachePostHref && isset($cachePostHref['href']) ){
            return $cachePostHref['href'];
        }

        $href = '';

        $customedSlug = attr($post, 'customedSlug');
        $type = attr($post, 'type');
        $slug = attr($post, 'slug');
        $acfLinkType = attr(b()->findOneBy([['type', '==', 'acf'],['name','==',$type]])->fetch(), 'linkType'); // acf linkType is PRIOR on post linkType
        $linkType = attr($post, 'linkType');
        $finalLinkType = $acfLinkType ?? $linkType;

        if( !isset($this->collection) ){
            $this->collection = $this->please->getRepo('bloggy')->fetchEager(
                b()->findAllBy([
                    ['type', '==', 'page'],
                    'or',
                    ['inMenu', 'in', ['on', 'yes']]
                ])->fetch()
            );
        }

        if ($this->collection) {
            $href = $this->_getRecursivePostHref($post, $this->collection);
        }

        // Adding the slug of the current post
        $href = $href . (!empty($customedSlug) ? $customedSlug : '/' . $slug);

        // Beautifying the href in case of "homepage" with slug matching (/home|/accueil)
        $href = ($href === '/home' || $href === '/accueil' || $href === '/' || $href === '') ? '/' : $href;

        // Adding article prefix (default as html)
        $href .= ($finalLinkType == 'article') ? '-' . attr($post, 'id') . '.html' : '';

        //linkType == 'page' or attr(b, 'type') == 'page' or attr(params, 'parent') == 'null'

        $url = $this->please->serve('url')->getUrl($href);

        $this->please->serve('crud')->basicCreate([
            'collection' => 'bloggies',
            'sanitizeRequest' => false,
            'sanitizer' => function() use ($postId, $url) {
                return ['type' => 'href', 'postId' => $postId, 'href' => $url];
            }
        ]);
        return $url;
    }
    
    public function redirectToHome()
    {
        return $this->redirect(
            $this->getHome()
        );
    }

    public function getCurrentUrlParamsLess(): string
    {
        return $this->getHandledUrl();
    }

    public function getBaseUrl()
    {
        return $this->please->serve('env')->getAppBaseUrl();
    }

    private function getHandledUrl()
    {
        //$this->getRouterContext()->getPathInfo()

        $app_base_url = $this->getBaseUrl();

        //localhost
        //so lets remove app_dir
        if (false !== strpos($app_base_url, 'http://localhost')) {
            $exploded = explode('/', $app_base_url);
            $app_dir = end($exploded);
            $app_base_url = str_ireplace($app_dir, '', $app_base_url);
            //$app_base_url = preg_replace('~//+~', '/', $app_base_url);
        }
        return trim($app_base_url, '/') . '/' . trim($this->getRouterContext()->getPathInfo(), '/');
    }

    public function isDev()
    {
        return $this->getEnv() == 'dev';
    }

    public function getEnv($var='APP_ENV')
    {
        return $this->please->serve('env')->getAppEnv($var);
    }

    public function isLocalHost()
    {
        return $this->please->serve('env')->isLocalHost();
    }

    public function isBackoffice()
    {
        if(
            //strpos($this->please->getReferer(), $this->getUrl('_admin')) !== false
            strpos($this->getCurrentUrlParamsLess(), $this->getUrl('_admin')) !== false
            ||
            $this->isPageBuilderMode()
        ) {
            return true;
        }
        return false;
    }

    public function isPageBuilderMode()
    {
        return strpos($this->getQueryString(), 'swagg') !== false;
    }

    protected function _getRecursivePostHref($post, $collection)
    {   
        //if( !isset($this->bloggyStore) ){ $this->bloggyStore = b(); }
        if( !isset($this->bloggyRepo) ){ $this->bloggyRepo = $this->please->getRepo('bloggy'); }
        //if( !isset($this->bloggyStore) ){ $this->bloggyStore = $this->please->getMyNoSQLCollection('bloggies'); }
        if( isset($post[0]) ){ $post = $post[0]; }

        $parent = attr($post, 'parent');

        if( is_string($parent) ){
            //$parent = $this->bloggyStore->find($parent);
        }

        if( $parent ){
            foreach ($collection as $collect) {
                if( attr($collect, 'id') == $parent ){
                    $parent = $collect;
                }
            }
        }

        $href = '';
        
        foreach ($collection as $collect) {

            $collectId = attr($collect, 'id');
            $collectSlug = attr($collect, 'slug');
            
            if( $parent ){
                
                $parentId = attr($parent, 'id');
                $parentSlug = attr($parent, 'slug');

                if( $collectId == $parentId ){
                    
                    $href .= $collectSlug . '/';

                    return $this->_getRecursivePostHref(

                        $this->bloggyRepo->fetchEager(
                            b()->find($parentId)->fetch()
                        ), $collection

                    ) . '/' . $parentSlug;
                }
            }
        }
        return $href;
    }

}
