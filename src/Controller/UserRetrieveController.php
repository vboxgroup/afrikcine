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
class UserRetrieveController extends AbstractController
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
     * @Route("forgot", name="forgot", methods={"GET"})
     */
    public function forgot()
    {
        return $this->crudServ->read([
            'collection' > 'users',
            'isGranted' => [
                'byUserRoles' => ['__NONE__']
            ],
            'finder' => true,
            'onFound' => function ($user) {
                $this->please->setPost([ '_title' => 'Récupération de compte' ]);
                return $this->renderTpl('forgot');
            },
        ]);
    }

    /**
     * @Route("forgot", name="postForgot", methods={"POST"})
     */
    public function postForgot()
    {
        return $this->please->serve('user')->forgot([
            'finder' => function ($posted, $collection){
                return $collection->findOneBy(['_email','==',attr($posted, '_email')])->fetch();
            },
            'onNotFound' => function(){
                
                $errors['_email'] = 'Adresse email inconnue';

                if( $this->please->isXHR() ){
                    return $this->resServ->JsonResponse([ 'errors' => $errors ]);
                }
                $this->please->setFlash([ 'errors' => $errors ]);
                return $this->redirect($this->please->getReferer());
            },
            'onFound' => function ($user) {

                return $this->please->serve('crud')->basicUpdate([
                    'collection' => 'users',
                    'finder' => function() use ($user) {
                        return $user;
                    },
                    'sanitizer' => function($posted, $user){
                        // save forgotToken into db
                        return [
                            '_forgotToken' => sha1(uniqid())
                        ];
                    },
                    'onSuccess' => function($user){

                        return $this->please->sendEmail([
                            'subject' => 'Récupération de compte',
                            'from' => ['support@silveredovoui.com' => 'CAMBIAR.FR'],
                            'to' => [ attr($user, '_email') => $this->please->serve('user')->getFullName($user)],
                            'body' => function($mail, $mediaServ, $urlServ) use ($user) {

                                return "<p>Un processus de récupération de votre compte Cambiar.fr a été lancé.</p>
                                        <p>Si vous en êtes l'auteur, cliquez sur le bouton <b>Récupérer mon compte</b> ci-dessous ou ignorez simplement ce mail.</p>
                                        <div _align='center'>
                                            <a style='padding:10px 15px;background-color:#4CAF50;color:#fff;text-decoration:none;margin:5px 0;display:inline-block' href=".$urlServ->getUrl('users/retrieve/'.attr($user, '_forgotToken')).">Récupérer mon compte</a>
                                        </div>";

                                /*$dirServ = $this->please->serve('dir');

                                $mail->addEmbeddedImage($dirServ->dirPath('public/theme/assets/img/logo.png'), 'logo', 'logo.png');
                                $mail->addEmbeddedImage($dirServ->dirPath('public/theme/assets/flaticons/forgot.png'), 'flaticon', 'forgot.png');

                                $data = [
                                    'userFullname' => $this->please->serve('user')->getFullName($user),
                                    'title' => 'Récupération de compte',
                                    'body' => "
                                        <p>Un processus de récupération de votre compte Jo'place.com a été lancé.</p>
                                        <p>Si vous en êtes l'auteur, cliquez sur le bouton <b>Récupérer mon compte</b> ci-dessous ou ignorez simplement ce mail.</p>
                                        <div align='center'>
                                            <a style='padding:10px 15px;background-color:#4CAF50;color:#fff;text-decoration:none;margin:5px 0;display:inline-block' href=".$urlServ->getUrl('users/retrieve/'.attr($user, '_forgotToken')).">Récupérer mon compte</a>
                                        </div>
                                    ",
                                ];
            
                                return $this->renderTpl('forgot--email', compact('data'));*/
                            },
                            'onSuccess' => function($params) {
                                return $this->resServ->JsonResponse([
                                    'reload' => false,
                                    'cuteModal' => 'Email de récupération envoyée avec succès! <br>Consultez votre boîte électronique.'
                                ]);
                            },
                            'onError' => function($e) use ($user) {
                                return $this->resServ->JsonResponse([
                                    'e' => $e,
                                    'reload' => false,
                                    'cuteModal' => ['Oops! Une erreur inattendue est survenue. <br>Veuillez réessayer plus tard.', 'danger']
                                ]);
                            },
                        ]);
                    }
                ]);
            },
        ]);
    }
    
    /**
     * @Route("retrieve/{token}", name="retrieve")
     */
    public function retrieve($token)
    {
        return $this->crudServ->read([
            'isGranted' => [
                'byUserRoles' => ['__NONE__']
            ],
            'collection' => 'users',
            'finder' => function ($collection) use ($token) {
                return $collection->findOneBy(['_forgotToken', '==', $token])->fetch();
            },
            'onFound' => function ($user) {
                $this->please->setPost([ '_title' => 'Récupération de compte' ]);
                return $this->renderTpl('retrieve', compact('user'));
            },
        ]);
    }
    
    /**
     * @Route("password/{token}/reset", name="resetPsw")
    */
    public function resetPsw($token)
    {
        $this->errors = [];

        return $this->crudServ->basicUpdate([
            'isGranted' => [
                'byUserRoles' => ['__NONE__']
            ],
            'collection' => 'users',
            'finder' => function ($collection) use ($token) {
                return $collection->findOneBy(['_forgotToken','==',$token])->fetch();
            },
            'validator' => [
                'psw' => function($posted){
                    $psw = $posted['psw'] ?? null;
                    if ( !$psw || !preg_match('/(.+){6,20}/', $psw, $matches) ) {
                        //return $this->errors['psw'] = $this->errors['confirm_psw'] = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                        return $this->errors['psw'] = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                    }
                    if ( empty($psw) || empty($posted['confirm_psw']) ) {
                        return $this->errors['psw'] = $this->errors['confirm_psw'] = "Renseignez un mot de passe valide";
                    }
                    else if ( $psw !== $posted['confirm_psw'] ) {
                        return $this->errors['psw'] = $this->errors['confirm_psw'] = "Les deux mot de passe ne correspondent pas";
                    }
                }
            ],
            'onInvalid' => function(){
                if( $this->please->isXHR() ){
                    return $this->resServ->JsonResponse([ 'errors' => $this->errors ]);
                }
                $this->please->setFlash([ 'errors' => $this->errors ]);
                return $this->redirect($this->please->getReferer());
            },
            'sanitizer' => function($posted){
                
                $password = sha1(attr($posted, 'psw'));

                return [
                    '_password' => $password,
                    '_oldPassword' => $password,
                ];
            },
            'onSuccess' => function($user) {

                return $this->please->serve('user')->loginInstant([
                    'user' => $user,
                    'onSuccess' => function ($user) {
                        $message = 'Bienvenue ' .($user['_username'] ?: $user['_email'] ?: '' ?: $user['_telephone'] ?: '').'.';
                        if( $this->please->isXHR() ){
                            return $this->resServ->JsonResponse([
                                'gToast' => $message,
                                'stayConnectedKey' => $this->please->serve('string')->encrypt($user['_id'], 'stayConnectedKey')
                            ]);
                        }
                        $this->please->setFlash('message', $message);
                        return $this->redirect($this->please->getReferer());
                    }
                ]);
            }
        ]);
    }

    private function renderTpl(string $templateName, array $parameters = array(), $response = null)
    {
        return $this->please->cachableResponse(
            $this->renderView("layouts/{$templateName}.html.twig", $parameters, $response)
        );
    }
}