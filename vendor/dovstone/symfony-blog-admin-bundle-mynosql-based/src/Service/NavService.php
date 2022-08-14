<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Markup;

class NavService extends AbstractController
{
    private $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->bloggyRepo = $please->getRepo('bloggies');
        $this->bCollection = $please->getMyNoSQLCollection('bloggies');
    }

    public function getNav($params): string
    {
        $navKey = $this->please->serve('string')->getSlug('sys-nav-'.$params['nav']);
        //
        $storedNav = $this->please->getStorage($navKey);
        if( $storedNav ){
            return $storedNav;
        }

        $nav = $this->please->getRepo('bloggy')->fetchEager(
            $this->bCollection->findOneBy([
                ['type','==','menu'], ['slug','==',$params['nav']??'']
            ])->fetch()
        );

        if( $nav ){
            $_pagesIds = array_map('intval', json_decode($nav['pagesId'] ));
            $pages = [];
            if( $_pagesIds ){
                foreach($_pagesIds as $pId){
                    $pages[] = $this->please->getRepo('bloggy')->fetchEager(
                        $this->bCollection->findOneBy([
                            ['id','==',$pId],
                            ['linkType','!=','article'],
                        ])->fetch()
                    );
                    // lets push children too
                    $children = $this->please->getRepo('bloggy')->fetchEager(
                        $this->bCollection->findAllBy([
                            ['parent','==',$pId],
                            ['published','==','on'],
                            ['inMenu','==','on'],
                            'and',
                            [
                                [
                                    ['type','!=','acf'],
                                    ['linkType','==','page'],
                                ],
                                'or',
                                [
                                    ['type','==','page'],
                                ]
                            ]
                        ])->fetch()
                    );
                    if($children){
                        $pages = array_merge($pages, $children);
                    }
                }
            }
            //return $this->please->serve('dir')->buildTree(array_reverse($pages), 'list', null, array_merge($nav, $params));
            $nav = $this->please->serve('dir')->buildTree($pages, 'list', null, array_merge($nav, $params));

            $this->please->setStorage([[$navKey, function() use ($nav){return $nav;}]]);
            return $this->please->getStorage($navKey);
        }

        return '';
    }

    public function getBreadcrumb($params = []): string
    {
        $template = array_merge([
            'home' => '<i class="fa fa-home"></i>',
            'back' => '<a href="{{href}}">{{title}}</a>',
            'current' => '<span>{{title}}</span>',
        ], $params['template'] ?? []);

        $itemClassName = $params['itemClassName'] ?? '';

        $post = $this->please->getGlobal("post");

        if( isset($post['parent']) && is_numeric($post['parent']) ){
            $post['parent'] = $this->please->getRepo('bloggy')->fetchEager(
                $this->bCollection->findOneBy([
                    ['id','==',$post['parent'] ?? null]
                ])->fetch()
            );
        }

        $breadcrumbBuilt = '';
        $this->breadcrumbRecursively($post, $itemClassName);
        if (isset($this->breadcrumbBuilt)) {
            for ($i = sizeof($this->breadcrumbBuilt) - 1; $i > -1; $i--) {
                $breadcrumbBuilt .= $this->breadcrumbBuilt[$i];
            }
        }
        $this->breadcrumbBuilt = [];
        
        return '<li class="'.$itemClassName.'">
                    <a href="' . $this->please->serve('url')->getUrl() . '">' . $template['home'] . '</a>
                </li>' . ( !$this->please->serve('dir')->isHome() ? $breadcrumbBuilt : '' );
    }

    public function orderBy($collection, $field = 'rank', $sortOrder = 'asc')
    {
        if( $collection ){
            $keys = array_column($collection, $field);
            if($collection){
                if($keys){
                    array_multisort($keys, strtolower($sortOrder) == 'asc' ? SORT_ASC :  SORT_DESC, $collection);
                }
            }
        }
        return $collection;
        /*
            // lets reOrder according to "rank"
            $zeroRanked = [];
            $ranking = [];
            if( !empty($this->db) ){
                foreach ($collection as $collect) {
                    if(isset($collect['rank'])){
                        $rank = (int)$collect['rank'];
                        if($rank != 0 && $rank != '' ){
                            $ranking[$rank] = $collect;
                        }
                        else {
                            $zeroRanked[ (new \DateTime())->getTimestamp() ] = $collect;
                        }
                    }
                    else {
                        $zeroRanked[ (new \DateTime())->getTimestamp() ] = $collect;
                    }
                }
            }
            if( $ranking ){
                ksort($ranking);
                $collection = array_merge($zeroRanked, $ranking);
            }
            return $collection;
        */
    }

    public function getPostRelatives($post)
    {
        $createdAt = attr($post, 'createdAt');
        $date = is_string($createdAt) ? (new \DateTime($createdAt))->format('Y-m-d H:i:s') : $createdAt;
        
        $prev = $this->bCollection->findOneBy([
                    ['type','==',attr($post, 'type')],
                    ['published','==','on'],
                    ['createdAt','<',$date],
                ])->orderBy(['createdAt'=>'desc'])->fetch();
        
        $next = $this->bCollection->findOneBy([
                    ['type','==',attr($post, 'type')],
                    ['published','==','on'],
                    ['createdAt','>',$date],
                ])->orderBy(['createdAt'=>'asc'])->fetch();

        return (object)[
            'prev' => $prev,
            'next' => $next
        ];
    }

    public function isActive($post): string
    {
        $urlServ = $this->please->serve('url');
        $href = is_string($post) ? $post : $urlServ->getPostHref($post);
        return $href == trim($urlServ->getCurrentUrlParamsLess(), '/');
    }

    public function routeActiveClass($routeName, string $activeClass='active'): string
    {
        $currRoute = $this->please->getRequest()->get('_route');
        //$currRouteParams = $this->please->getRequest()->get('_route_params');
        if(is_string($routeName)){
            return $currRoute == $routeName ? $activeClass : '';
        }
        elseif(is_array($routeName) && in_array($currRoute, $routeName)){
            return $activeClass;
        }
        return '';
    }

    private function breadcrumbRecursively($parent, $itemClassName): void
    {   
        if ( !is_null($parent) ) {

            $parentHref = attr($parent, 'href');

            $url_match = (trim($parentHref, '/') === $this->please->serve('url')->getCurrentUrl());

            $item = !$url_match ? '<a href="' . $parentHref . '">' . attr($parent, 'title') . '</a>' : '<span>' . attr($parent, 'title') . '</span>';
            $active_class = $url_match ? ' class="'.$itemClassName.' active" ' : ' class="'.$itemClassName.'"';

            $this->breadcrumbBuilt[] = '<li' . $active_class . '>' . $item . '</li>';

            $pParent = attr($parent, 'parent');
            //if ($pParent !== null && $pParent !== 'null') {
            if ($pParent !== null && $pParent !== 'null' && !is_integer($pParent) && !is_int($pParent) && !is_string($pParent)) {
                //here we go again
                $pParent['href'] = $this->please->serve('url')->getPostHref($pParent);
                $this->breadcrumbRecursively($pParent, $itemClassName);
            }
        }
    }
}
