<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Controller;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Markup;

/**
 * @Route("_admin/xhr/")
 */
class BundleXHRController extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
    }

    /**
     * @Route("render/{filename}/{cacheId}", name="_render")
     */
    public function _render($filename, $cacheId)
    {
        $needle = $cacheId."p";

        if($skeletonParams = $this->please->getGlobal($needle)){
            return $this->getResponse($filename, $skeletonParams);
        }
        return $this->getResponse($filename);
    }

    /**
     * @Route("store/{cacheId}", name="_storeRendered", methods={"POST"})
     */
    public function _storeRendered($cacheId)
    {
        $rendered = $this->please->getRequestStackRequest()->get('rendered');
        $rendered = str_replace('$(function()', 'AddScript(function()', $rendered);
        $this->please->setGlobal([ $cacheId => $rendered ]);
        return new JsonResponse([ 'status' => 200 ]);
    }

    private function getResponse($filename, $skeletonParams = [])
    {
        $rendered = new Markup($this->container->get('twig')->render("xhr-rendering/$filename.html.twig", $skeletonParams),'UTF-8');
        return new JsonResponse([ 'fn' => $filename, 'rendered' => $rendered ]);
    }
}
