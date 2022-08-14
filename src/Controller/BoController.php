<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\BloggyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @Route("bo/bloggy/")
 */
class BoController extends AbstractController
{
    public $perPage = 15;

    public function __construct(PleaseService $please, BloggyRepository $bRepo, UserRepository $userRepo)
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
        $this->please->prevContainer->get('service.execute_before')->__run();
        $this->User = $this->please->serve('security')->getUser();
        $this->crud = $this->please->serve('crud');
        $this->resServ = $this->please->serve('response');
        $this->bRepo = $bRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("{type}/basic-create", name="createBOBloggy")
     */
    public function createBOBloggy($type)
    {
        return $this->please->serve('crud')->create([
            'collection' => 'bloggies',
            'sanitizer' => function ($posted) use ($type) {
                if(attr($posted, 'is_spam')){ return false; }
                return [
                    '_type' => $type,
                    '_slug' => $type
                ];
            },
            'onSuccess' => function () use ($type) {
                return new JsonResponse([
                    'reload' => false,
                    'resetForm' => true,
                    'cuteModal' => [
                        'inbox' => 'Merci de nous avoir contacté. <br>Nous vous reviendrons sous peu.',
                        'newsletter' => "Vous recevrez désormais toutes nos meilleures offres.",
                        'place-book' => "Votre réservation a été effectuée. <br>Nous vous reviendrons.",
                    ][$type]
                ]);
            }
        ]);
    }

    /**
     * @Route("basic-update", name="basicUpdateBOBloggy")
     */
    public function basicUpdateBOBloggy()
    {
        return $this->please->serve('crud')->basicUpdate([
            'collection' => 'bloggies',
            'ifNotXML' => 'home',
            'isGranted' => [
                'byUserRoles' => ['admin'],
                'otherwise' => function(){
                    return $this->redirectToRoute('_authAdmin');
                }
            ],
            'finder' => function($collection){
                $id = $this->please->getRequestStackRequest()->get('id');
                return $collection->find($id);
            },
            'sanitizer' => function ($posted) {
                return [
                    'statut' => $posted['statut'] ?? 'pending'
                ];
            },
            'onSuccess' => function () {
                return new JsonResponse([
                    'reload' => false,
                    'gToast' => 'Données mises à jour'
                ]);
            }
        ]);
    }

    /**
     * @Route("{type}/list", name="listBOBloggy")
     */
    public function listBOBloggy($type)
    {    
        return $this->please->serve('crud')->readList([
            'collection' => 'bloggies',
            'isGranted' => [
                'byUserRoles' => ['admin', 'editor', 'moderator'],
                'otherwise' => function(){
                    return $this->redirectToRoute('_authAdmin');
                }
            ],
            'finderCriteria' => function($collection) use ($type) {
                $criteria = [['type', '==', $type]];
                $q = $this->please->getRequestStackQuery()->get('q');
                if($q){
                    $words = explode(' ', $q);
                    $c = [];
                    foreach ($words as $i => $word) {
                        if(!empty($word)){
                            $c[] = ['_id','like',$word];
                            $c[] = ['_title','like',$word];
                            if( $i < count($words)-1 ){
                                //$c[] = 'OR';
                            }
                        }
                    }
                    $criteria = array_merge($criteria, $c);
                }
                return [
                    $criteria, 
                    ['createdAt' => 'desc']
                ];
            },
            'perPage' => $this->perPage,
            'view' => function($items, $knpPaginator, $totalCount) use ($type) {

                $url = $this->generateUrl('createBOBloggy', ['type' => $type]);

                $data = [
                    'is' => 'list',
                    'knpPaginator' => $knpPaginator,
                    'items' => $this->bRepo->pop($items),
                ];

                return $this->please->serve('template')->getTpl($type, compact('data'));
            }
        ]);
    }

    /**
     * @Route("users/{role}/list", name="listBOUser")
     */
    public function listBOUser($role)
    {    
        return $this->please->serve('crud')->readList([
            'collection' => 'users',
            'isGranted' => [
                'byUserRoles' => ['admin'],
                'otherwise' => function(){
                    return $this->redirectToRoute('_authAdmin');
                }
            ],
            'finderCriteria' => function($collection) use ($role) {
                $criteria = [['_role', '==', $role]];
                $q = $this->please->getRequestStackQuery()->get('q');
                if($q){
                    $words = explode(' ', $q);
                    $c = [];
                    foreach ($words as $i => $word) {
                        if(!empty($word)){
                            $c[] = ['_username','like','%'.$word.'%'];
                            $c[] = 'OR';
                            $c[] = ['_firstname','like','%'.$word.'%'];
                            $c[] = 'OR';
                            $c[] = ['_lastname','like','%'.$word.'%'];
                            $c[] = 'OR';
                            $c[] = ['_telephone','like','%'.$word.'%'];
                            $c[] = 'OR';
                            $c[] = ['_mle','like','%'.$word.'%'];
                        }
                    }
                    $criteria = array_merge($criteria, $c);
                }
                return [
                    $criteria,
                    ['_createdAt' => 'desc']
                ];
            },
            'perPage' => $this->perPage,
            'view' => function($users, $paginator, $totalCount) use ($role) {

                $users = $this->userRepo->pop($users);

                $data = [
                    'is' => 'list',
                    'paginator' => $paginator,
                    'users' => $users
                ];

                return $this->please->serve('template')->getTpl($role, compact('data'));
            }
        ]);
    }
}
