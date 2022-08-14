<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @Route("users/")
 */
class UserRelatedController extends AbstractController
{
    public function __construct(PleaseService $please, UserRepository $userRepo)
    {
        $this->please = $please;
        //
        $this->please->serve('execute_before')->appExecBeforeService();
        $this->please->prevContainer->get('service.execute_before')->__run();
        //
        $this->userRepo = $userRepo;
        //
        $this->user = $this->please->serve('security')->getUser();
        $this->userId = attr($this->user, '_id');
        //
        $this->urlServ = $this->please->serve('url');
        $this->crudServ = $this->please->serve('crud');
        $this->resServ = $this->please->serve('response');
    }

    /**
     * @Route("{type}/create-related", name="createUserRelated")
     */
    public function createUserRelated($type)
    {
        return $this->crudServ->basicCreate([
            'collection' > 'bloggies',
            'isGranted' => $this->isGranted,
            'sanitizer' => function () use ($type) {
                return [
                    '_title' => $type.'-'.uniqid(),
                    '_type' => $type,
                    '_user' => (int)$this->userId
                ];
            },
            'onSuccess' => function () use ($type) {
                return $this->resServ->JsonResponse([
                    'cuteModal' => [
                        'address' => 'Adresse de livraison ajoutée.',
                    ][$type]
                ]);
            },
        ]);
    }

    /**
     * @Route("{type}/{id}/update-related", name="updateUserRelated")
    */
    public function updateUserRelated($type, $id)
    {
        return $this->crudServ->basicUpdate([
            'collection' > 'bloggies',
            'isGranted' => $this->isGranted,
            'finder' => function($collection) use ($id) { return $collection->find($id); },
            'onSuccess' => function($document){
                return $this->resServ->JsonResponse([
                    'cuteModal' => 'Vos données ont été mises à jour.'
                ]);
            }
        ]);
    }
    
    /**
     * @Route("basic-update", name="basicUpdateUserInfo")
     */
    public function basicUpdateUserInfo()
    {
        return $this->crudServ->basicUpdate([
            'isGranted' => [
                'byUserRoles' => ['master_admin'],
                'byUserId' => $this->userId
            ],
            'collection' => 'users',
            'finder' => function($store) {
                $id = ( $this->please->getRequestStackRequest()->get('id') ?? $this->userId );
                return $store->findById($id);
            },
            'onSuccess' => function ($user) {
                return $this->please->serve('user')->loginInstant([
                    'user' => $user,
                    'onSuccess' => function ($user) {
                        return $this->resServ->JsonResponse([
                            'cuteModal' => 'Vos données ont été mises à jour.',
                        ]);
                    }
                ]);
            }
        ]);
    }

    /**
     * @Route("{id}/delete-related", requirements={"id":"[0-9]+"}, name="deleteUserRelated")
     */
    public function deleteUserRelated($id)
    {
        return $this->crudServ->delete([
            'collection' > 'bloggies',
            'isGranted' => $this->isGranted,
            'finder' => function ($collection) use ($id) { return $collection->find($id); },
            'onSuccess' => function () {
                return $this->resServ->JsonResponse([
                    'cuteModal' => 'Données définitivement supprimées.'
                ]);
            },
        ]);
    }

    private function renderTpl(string $templateName, array $parameters = array(), $response = null)
    {
        return $this->please->cachableResponse(
            $this->renderView("{$templateName}.html.twig", $parameters, $response)
        );
    }
}