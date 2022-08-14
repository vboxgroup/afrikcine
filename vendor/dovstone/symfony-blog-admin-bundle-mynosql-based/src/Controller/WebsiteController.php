<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Controller;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @Route("/")
 */
class WebsiteController extends AbstractController
{
    public function __construct(PleaseService $please )
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
    }
    
    /**
     * @Route("", name="_getHomePage")
     */
    public function _getHomePage()
    {
        $this->please->serve('execute_before')->appExecBeforeService();
        //
        $page = $this->please->getRepo('bloggy')->fetchEager(
            b()->findOneBy([
                ['type','==','page'],
                ['slug','in', ['home', '/', 'accueil']],
                ['published','==','on']
            ])->fetch()
        );
        if($page){
            return $this->render200($page);
        }
        return $this->render404();
    }

    /**
     * @Route("{slug}", requirements={"slug":"[a-z-/0-9]+"}, name="_getPage")
     */
    public function _getPage($slug)
    {
        $slug_ = $slug;
        //
        $slug = explode('/', trim($slug, '/'));
        $slug = end($slug);

        $pages = $this->please->getRepo('bloggy')->fetchEager(
            b()->findAllBy([
                ['type','!==','acf'],
                ['slug','==',$slug],
                ['linkType','!=','none'],
                ['published','==','on']
            ])->fetch()
        );

        if ($pages) {
            return $this->browsePostsFound($pages, $slug);
        }
        return $this->render404();
    }

    /**
     * @Route("{parent_slug}/{slug}-{id}.html", requirements={"parent_slug":"[a-z-/0-9]+", "slug":"[a-z-/0-9]+", "id":"[0-9]+"}, name="_getArticleWithParent")
     */
    public function _getArticleWithParent($parent_slug, $slug, $id)
    {
        $slug = explode('/', $slug);
        $slug = end($slug);

        $posts = $this->please->getRepo('bloggy')->fetchEager(
            b()->findAllBy([
                ['id','==',$id],
                ['slug','==',$slug],
                ['published','==','on']
            ])->fetch()
        );

        if (!$posts) {
            return $this->render404();
        }

        return $this->browsePostsFound($posts, $parent_slug . '/' . $slug);
    }

    /**
     * @Route("{slug}-{id}.html", requirements={"slug":"[a-z-/0-9]+", "id":"[0-9]+"}, name="_getArticleWithOutParent")
     */
    public function _getArticleWithOutParent($slug, $id)
    {
        $posts = $this->please->getRepo('bloggy')->fetchEager(
            b()->findAllBy([
                ['id','==',$id],
                ['slug','==',$slug],
                ['published','==','on']
            ])->fetch()
        );

        if (!$posts) {
            return $this->render404();
        }

        return $this->browsePostsFound($posts, $slug);
    }

    private function browsePostsFound($posts, $slug)
    {
        $urlServ = $this->please->serve('url');
        $navServ = $this->please->serve('nav');
        $tplServ = $this->please->serve('template');


        foreach ($posts as $post) {

            $post['href'] = $urlServ->getPostHref($post);
            
            $relatives = $navServ->getPostRelatives($post);
            $post['prev'] = $relatives->prev;
            $post['next'] = $relatives->next;
            if($post['parent']){
                $post['related'] = b()->findBy([
                    ['parent','==',$post['parent']['id']],
                    ['type','==',$post['type']]
                ])->limit(2)->fetch();
            }

            $href = $tplServ->sanitizeViewLinks($post['href']);

            if ( $href == $urlServ->getCurrentUrlParamsLess()) {
                return $this->render200($post);
            }
        }
        return $this->render404();
    }
    
    private function render200($post)
    {
        $envServ = $this->please->serve('env');

        /*$postServ = $this->please->serve('post');

        $sibling = $postServ->getPostRelatives($post);

        $post->descPages = $sibling->pages;
        $post->descArticles = $sibling->articles;
        $post->prevArticle = $sibling->prevArticle;
        $post->nextArticle = $sibling->nextArticle;*/
        
        $post = $this->please->serve('template')->hookPost($post);

        $post['fullTitle'] = ($post['secondTitle'] ?? $post['title'] ?? ''). ' • ' .$envServ->getAppEnv('APP_NAME');

        if ( 
            !$this->please->getRequestStackQuery()->get('swagg')
            &&
            (
                $this->please->isXHR()
                && $this->please->getRequestStackQuery()->get('_ajaxify')
                && $envServ->getAppEnv('DEEP_CACHE') == 'true'
            )
        ){

            $key = $this->please->serve('string')->getSlug($this->please->serve('url')->getCurrentUrl());
            $key = trim($key, '-ajaxify-true');

            $view = $this->please->setStorage([
                [ $key, function() use ($post) {
            
                    $this->please->setGlobal([ "post" => $post ]);
            
                    $layout = attr($post, 'layout', 'single');
            
                    if( $layout == 'none' || attr($post, 'linkType') == 'article' ){ $layout = 'single'; }

                    $layoutFile = $this->please->serve('dir')->getThemeDirAbsDirPath('layouts')."/$layout.html.twig";

                    $layout = (new Filesystem())->exists($layoutFile) ? "layouts/$layout.html.twig" : "@DovStoneSymfonyBlogAdminBundleMyNoSQLBased/partials/website-default.html.twig";

                    return $this->renderView($layout);
                } ]
            ]);
            //
            $view = $this->please->getStorage($key);
        }
        else { 

            $post['extraData'] = json_decode($post['extraData']??null, true) ?? null;

            $this->please->setGlobal([ "post" => $post ]);

            $layout = attr($post, 'layout', 'single');
            $linkType = attr($post, 'linkType');
            $layoutSingle = attr($post, 'parent.layoutSingle', 'single');

            if( !in_array($layoutSingle, ['single', 'default']) ){ $layout = $layoutSingle; }
            else if( ($layout == 'none' || $layout == 'default') && $linkType == 'article' ){ $layout = 'single'; }
    
            $layoutFile = $this->please->serve('dir')->getThemeDirAbsDirPath('layouts')."/$layout.html.twig";

            //
            $view = $this->renderView(
                (new Filesystem())->exists($layoutFile) ? "layouts/$layout.html.twig" : "@DovStoneSymfonyBlogAdminBundleMyNoSQLBased/partials/website-default.html.twig"
            );
        }
        return $this->please->cachableResponse($view);
    }

    private function render404()
    {
        $fileSystem = new Filesystem();

        $_404path = $this->please->serve('dir')->getThemeDirAbsDirPath('layouts')."/404.html.twig";

        return new Response($this->please->serve('template')->sanitizeView(
            $this->renderView( $fileSystem->exists($_404path) ? "layouts/404.html.twig" : "@DovStoneSymfonyBlogAdminBundleMyNoSQLBased/partials/website-404.html.twig")
        ), 404);
    }
}
