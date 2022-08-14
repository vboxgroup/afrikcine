<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Markup;

class XHRService extends AbstractController
{
    private $fileSystem;
    private $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function rendering($filename, $skeletonParams, $waitXHR = true)
    {
        $skeletonParams = array_merge([
            'id' => uniqid(),
            'classNames' => '',
            'skeletonClassNames' => '',
            'blocksCount' => [],
            'times' => 1,
            'cachable' => false,
            'children' => []
        ], $skeletonParams);

        $id = $skeletonParams['id'];
        $xhrId = substr(sha1($skeletonParams['id']), 0, 8);
        $classNames = $skeletonParams['classNames'];
        $skeletonClassNames = $skeletonParams['skeletonClassNames'];
        $blocksCount = $skeletonParams['blocksCount'];
        $times = $skeletonParams['times'];
        $children = $skeletonParams['children'];
        $isCachable = $skeletonParams['cachable'];
        $ajaxSuccess = $skeletonParams['ajax']['success'] ?? null;
        $ajaxError = $skeletonParams['ajax']['error'] ?? null;

        $xhrId = substr(sha1($skeletonParams['id']), 0, 8);
        //$this->please->setStorage([ $xhrId => null ]);

        // cached ?
        $this->cache = $this->please->getStorage($xhrId);

        // lets Store params so we can reUse theme in
        // vendor/dovstone/symfony-blog-admin-bundle-mynosql-based/src/Controller/BundleXHRController->_renderXHR()
        $this->please->setGlobal([ $xhrId."p" => $skeletonParams ]);

        if($this->please->isXHR()){

            if( $this->cache ){ return $this->getCache(); }

            $skeleton = $this->getSkeleton($id, $blocksCount, $skeletonClassNames, $times);

            $view = $this->sendAjaxTemplate($skeleton, $filename, $xhrId, $classNames, $isCachable, $ajaxSuccess, $ajaxError);
        }
        else {
            
            if( $this->cache ){ return $this->getCache(); }

            $view = "<div class=\"$classNames is-not-xhr\">"
                        .$this->container->get('twig')->render("xhr-rendering/$filename.html.twig", $skeletonParams).
                    "</div>";
        }
        //
        $view = new Markup($view, 'UTF-8');

        // lets create a cache only when isNotXHR
        if($isCachable && $this->please->isNotXHR()){
            $this->please->setGlobal([ $xhrId => $view ]);
        }
        return $view;
    }

    private function getSkeleton($id, $blocksCount, $skeletonClassNames, $times)
    {
        $skeleton = 'Chargement...';
        if( $blocksCount ){
            $skeleton = '';
            //$skeleton = '<div class="skeleton-wraper skeleton-wrapper--'.$id.'">';
            for ($i=0; $i < $times; $i++) {
                $skeleton .= '<div class="skeleton-wrapper '.$skeletonClassNames.'">';
                for($j=1; $j < $blocksCount+1; $j++){ $skeleton .= '<div class="skeleton--'.$j.'"></div>';}
                $skeleton .= '</div>';
            }
            //$skeleton .= '</div>';
        }
        return $skeleton;
    }

    private function sendAjaxTemplate($skeleton, $filename, $xhrId, $classNames, $isCachable, $ajaxSuccess, $ajaxError)
    {
        $selector = uniqid('xhr');

        $url = $this->please->serve('url')->getUrl("_admin/xhr/render/$filename/$xhrId");

        $tpl = "<div class=\"$selector $classNames is-xhr\">$skeleton</div><script>
            $.ajax({url:'$url',success:function(res){var \$self=$('.$selector');\$(\".$selector\").html(res.rendered);LessToTextLess.init();$ajaxSuccess;ExecAddedScripts('XHRService->sendAjaxTemplate()');";
            if( $isCachable ){
                $url = $this->please->serve('url')->getUrl("_admin/xhr/store/$xhrId");
                $tpl .= "$.ajax({url:'$url',type:'post',data:{rendered:res.rendered}})";
            }
        $tpl .= "},error:function(err){{$ajaxError};console.error(err);}})</script>";
        return $tpl;
    }

    private function getCache()
    {
        return new Markup($this->cache, 'UTF-8');
        //return new Markup($this->cache."<script>ExecAddedScripts('XHRService->getCache()');</script>", 'UTF-8');
    }
}
