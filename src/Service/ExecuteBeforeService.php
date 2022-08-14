<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

class ExecuteBeforeService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function __run()
    {
      $this->please->setBackOfficeNav([
        ['Inbox', 'inbox', $this->generateUrl('listBOBloggy', ['type' => 'inbox'])],
        ['Réservations', 'inbox', $this->generateUrl('listBOBloggy', ['type' => 'place-book'])],
      ]);
    }

    public function __hookView($view)
    {
    }

    public function __hookPost($post)
    {
    }

    public function __defaultFallbacks()
    {
      $home = $this->please->serve('url')->getUrl();
      return [
        'otherwise' => function() use ($home) {
          return $this->redirect($home);
          return $this->fallback('Accès Interdit');
        },
        'onNotFound' => function() use ($home) {
          return $this->redirect($home);
          return $this->fallback('Donnée introuvable');
        }
      ];
    }
}
