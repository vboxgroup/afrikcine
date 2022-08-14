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
class UserInOutController extends AbstractController
{
    public function __construct(PleaseService $please, UserRepository $userRepo)
    {
        $this->please = $please;
        //
        $this->please->serve('execute_before')->appExecBeforeService();
        $this->please->prevContainer->get('service.execute_before')->__run();
        $this->please->setGlobal(['errors' => []]);
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
     * @Route("auth", name="authUser")
     */
    public function authUser()
    {
        return $this->please->serve('user')->login([
            'collection' => 'users',
            'onAlreadyLoggedIn' => function($user){
                return $this->resServ->JsonResponse([
                    'authed' => true,
                    'gToast' => "Vous êtes déja connecté en tant que " . ($user['_username'] ?: $user['_email'] ?: '') .'.'
                ]);
            },
            'finder' => function ($posted, $collection) {

                $ident = attr($posted, 'ident');
                $psw = sha1(attr($posted, 'password'));

                if( attr($posted, 'stay-connected') ){ $this->stayConnected = true; }

                $attempt = $collection->findOneBy([
                    ['_telephone', '==', $ident],
                    ['_password', '==', $psw],
                ])->fetch();
                if( !$attempt ){
                    $attempt = $collection->findOneBy([
                        ['_email', '==', $ident],
                        ['_password', '==', $psw],
                        ['_approved', '==', 'on'],
                    ])->fetch();
                }
                if( !$attempt ){
                    $attempt = $collection->findOneBy([
                        ['_username', '==', $ident],
                        ['_password', '==', $psw],
                        ['_approved', '==', 'on']
                    ])->fetch();
                }
                return $attempt;
            },
            'formView' => function ($userWasNotFound) {

                if( $this->please->isXHR() ){
                    return $this->resServ->JsonResponse([
                        'error' => true,
                        'authVerdirct' => 'auth-credentials-denied'
                    ]);
                }
                $this->please->setFlash([ 'errors' => [ 'ident' => 'Identifiants incorrects' ]]);
                return $this->redirect($this->please->getReferer());
            },
            'onSuccess' => function ($user) {
                if( $this->please->isXHR() ){
                    $res = [
                        'gToast' => 'Bienvenue ' .($user['_username'] ?: $user['_email'] ?: $user['_telephone'] ?: '').'.',
                        'reloadDeep' => true
                    ];
                    if( isset($this->stayConnected) ){
                        $res['sck'] = $this->please->serve('string')->encrypt($user['_id'], $password='stayConnectedKey');
                    }
                    return $this->resServ->JsonResponse($res);
                }
                return $this->redirect($this->please->serve('url')->getUrl());
            },
        ]);
    }

    /**
     * @Route("logout", name="logoutUser")
     */
    public function logoutUser()
    {
        return $this->please->serve('user')->logout([
            'onAlreadyLoggedOut' => function(){ return $this->redirect($this->resServ->getRedirectUrl()); },
            'onSuccess' => function($user){
                if( $this->please->isXHR() ){
                    return $this->resServ->JsonResponse([
                        'cuteModal' => 'A très bientôt ' .($user['_username'] ?: $user['_email'] ?: '').'.'
                    ]);
                }
                return $this->redirect($this->resServ->getRedirectUrl());
            }
        ]);
    }

    /**
     * @Route("sck", name="stayUserConnected", methods={"POST"})
     */
    public function stayUserConnected()
    {
        return $this->please->serve('user')->login([
            'finder' => function ($posted, $collection) {
                $sck = attr($posted, 'sck');
                $userId = $this->please->serve('string')->decrypt($sck, $password='stayConnectedKey');
                if($userId){
                    return $collection->find($userId)->fetch();
                }
                return false;
            },
            'onAlreadyLoggedIn' => function($user){
                return $this->resServ->JsonResponse([ 'message' =>  'Already logged in' ]);
            },
            'formView' => function ($user) {
                return $this->resServ->JsonResponse([ 'message' =>  'No session found' ]);
            },
            'onSuccess' => function ($user) {
                return $this->resServ->JsonResponse([
                    'refresh' =>  true,
                    'message' =>  'Logged in successfully'
                ]);
            }
        ]);
    }

    private function renderTpl(string $templateName, array $parameters = array(), $response = null)
    {
        return $this->please->cachableResponse(
            $this->renderView("{$templateName}.html.twig", $parameters, $response)
        );
    }
}