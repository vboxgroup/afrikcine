<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ResponseService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }
    
    public function getRedirectUrl()
    {
        $r = $this->redirectTypes();
        return $r->r1 ?? $r->r2 ?? $r->r3 ?? $r->r4 ?? $this->please->serve('url')->getUrl();
    }
    
    public function JsonResponse($response)
    {
        $r = $this->redirectTypes();
        $r = 
            $r->r1||$r->r2||$r->r3||$r->r4||$r->r5||$r->r6
            ? 
            [ 
                $r->r1||$r->r2||$r->r3
                ? 'redirectDeep'
                : 'redirect' => $r->r1 ?? $r->r2 ?? $r->r3 ?? $r->r4 ?? $r->r5 ?? $r->r6
            ]
            :
            [];
            
        $rel = $this->reloadTypes();
        $rel = $rel->rel1||$rel->rel2 ? ['reload' => $rel->rel1 ?? $rel->rel2] : [];

        return new JsonResponse(array_merge($response, $r, $rel));
    }
    
    private function redirectTypes()
    {
        return (object) [
            'r1' => $this->please->getRequestStackQuery()->get('_redirectDeep') ?: null,
            'r2' => $this->please->getRequestStackRequest()->get('_redirectDeep') ?: null,
            'r3' => $this->please->getRefererParam('_redirectDeep') ?: null,

            'r4' => $this->please->getRequestStackQuery()->get('_redirect') ?: null,
            'r5' => $this->please->getRequestStackRequest()->get('_redirect') ?: null,
            'r6' => $this->please->getRefererParam('_redirect') ?: null,
        ];
    }
    
    private function reloadTypes()
    {
        return (object) [
            'rel1' => $this->please->getRequestStackQuery()->get('_reload'),
            'rel2' => $this->please->getRequestStackRequest()->get('_reload'),
        ];
        /*return (object) [
            'r1' => $this->please->getRefererParam('_redirectDeep') ?: null,
            'r2' => $this->please->getRequestStackQuery()->get('_redirectDeep'),
            'r3' => $this->please->getRefererParam('_redirect') ?: null,
            'r4' => $this->please->getRequestStackQuery()->get('_redirect'),
        ];*/
    }
}
