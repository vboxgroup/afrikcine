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
class UserCRUDController extends AbstractController
{
    public function __construct(PleaseService $please, UserRepository $userRepo)
    {
        $this->please = $please;
        //
        $this->please->serve('execute_before')->appExecBeforeService();
        $this->please->prevContainer->get('service.execute_before')->__run();
        $this->userRepo = $userRepo;
        $this->user = $this->please->serve('security')->getUser();
        $this->userId = attr($this->user, '_id');
        $this->urlServ = $this->please->serve('url');
        $this->crudServ = $this->please->serve('crud');
        $this->resServ = $this->please->serve('response');
    }

    /**
     * @Route("{role}/create", name="createUser")
     */
    public function createUser($role, $updateParams=[])
    {
        $this->errors = [];

        return $this->crudServ->create([
            'isGranted' => [
                'byUserRoles' => ['__NONE__']
            ],
            'validator' => [
                '_username' => function($posted){
                    if(empty($posted['_username'])){
                        return $this->errors['_username'] = "Renseignez un nom d'utilisateur valide";
                    }
                },
                /*'_email' => function($posted){
                    if (!filter_var($posted['_email'], FILTER_VALIDATE_EMAIL)) { 
                        return $this->errors['_email'] = "Renseignez une adresse email valide";
                    }
                },*/
                '_telephone' => function($posted){
                    $telephone = $posted['_telephone'] ?? null;
                    if(!$telephone || !preg_match('/([0-9]{10})/', $telephone)){
                        return $this->errors['_telephone'] = "Renseignez un numéro de téléphone valide.<br> (10 chiffres)";
                    }
                },
                'psw' => function($posted){
                    $psw = $posted['psw'] ?? null;
                    if ( !$psw || !preg_match('/(.+){6,20}/', $psw, $matches) ) {
                        //return $this->errors['psw'] = $this->errors['confirm_psw'] = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                        return $this->errors['psw'] = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                    }
                    /*if ( empty($psw) || empty($posted['confirm_psw']) ) {
                        return $this->errors['psw'] = $this->errors['confirm_psw'] = "Renseignez un mot de passe valide";
                    }
                    else if ( $psw !== $posted['confirm_psw'] ) {
                        return $this->errors['psw'] = $this->errors['confirm_psw'] = "Les deux mot de passe ne correspondent pas";
                    }*/
                }
            ],
            'onInvalid' => function(){
                if( $this->please->isXHR() ){
                    return $this->resServ->JsonResponse([ 'errors' => $this->errors ]);
                }
                $this->please->setFlash([ 'errors' => $this->errors ]);
                return $this->redirect($this->please->getReferer());
            },
            'collection' => 'users',
            'sanitizer' => function($posted) use ($role) {
                
                $password = sha1(attr($posted, 'psw'));

                return [
                    '_roles' => [strtolower($role)],
                    '_password' => $password,
                    '_oldPassword' => $password,
                    '_approved' => ($role == 'merchant' ? 'off' : 'on'),
                    '_enabled' => 'on'
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

    /**
     * @Route("{id}/update", name="updateUser")
    */
    public function updateUser($id)
    {
        $this->errors = [];

        return $this->crudServ->update([
            'collection' => 'users',
            'isGranted' => [
                'byUserRoles' => ['__ANY__']
            ],
            'validator' => [
                '_username' => function($posted){
                    if(!$posted['_username']){
                        return $this->errors['_username'] = "Renseignez un nom d'utilisateur valide";
                    }
                },
                '_firstname' => function($posted){
                    if(!$posted['_firstname']){
                        return $this->errors['_firstname'] = "Renseignez vo(s)tre prénom(s)";
                    }
                },
                '_lastname' => function($posted){
                    if(!$posted['_lastname']){
                        return $this->errors['_lastname'] = "Renseignez votre nom";
                    }
                },
                '_telephone' => function($posted){
                    $telephone = $posted['_telephone'] ?? '';
                    if(!$telephone || !preg_match('/([0-9]{10})/', $telephone)){
                        return $this->errors['_telephone'] = "Renseignez un numéro de téléphone valide.<br> (10 chiffres)";
                    }
                },
                '_email' => function($posted){
                    $email = $posted['_email'] ?? null;
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return $this->errors['_email'] = "Renseignez une adresse email valide";
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
            'finder' => function($collection) use ($id) {
                return $collection->find($id)->fetch();
            },
            'onNotFound' => function(){
                return $this->urlServ->redirectToHome();
            },
            'onSuccess' => function($user) {
                return $this->please->serve('user')->loginInstant([
                    'user' => $user,
                    'onSuccess' => function ($user) {

                        $message = 'Vos données ont été mises à jour.';
                        
                        if( $this->please->isXHR() ){
                            return $this->resServ->JsonResponse([
                                'cuteModal' => $message,
                                'reload' => true
                            ]);
                        }
                        $this->please->setFlash('alert', '<i class="fa fa-check-circle"></i> '.$message);
                        return $this->redirect($this->please->getReferer());
                    }
                ]);
            }
        ]);
    }

    /**
     * @Route("password", name="updatePsw")
    */
    public function updatePsw()
    {
        $this->errors = [];

        return $this->crudServ->basicUpdate([
            'collection' => 'users',
            'isGranted' => ["byUserRoles" => ['__ANY__']],
            'collection' => 'users',
            'validator' => [
                'psw' => function ($posted) {
                    if ( sha1(attr($posted, 'psw')) !== attr($this->please->getUser(), '_password') ) {
                        return $this->errors['psw'] = 'Mot de passe actuel incorrect';
                    }
                },
                'new_psw' => function ($posted) {
                    $new_psw = $posted['new_psw'] ?? null;
                    if ( !$new_psw || !preg_match('/(.+){6,20}/', $new_psw, $matches) ) {
                        $msgLen = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                    }
                    $confirm_new_psw = $posted['confirm_new_psw'] ?? null;
                    if ( !$confirm_new_psw || !preg_match('/(.+){6,20}/', $confirm_new_psw, $matches) ) {
                        $msgLen = "Renseignez un mot de passe valide.<br> (6 à 20 caractères)";
                    }

                    if (isset($msgLen)) {
                        $msg = $msgLen;
                    }
                    if ($new_psw !== $confirm_new_psw) {
                        $msg = 'Les mots de passe ne correspondent pas';
                    }
                    if(isset($msg)){
                        return $this->errors['new_psw'] = $this->errors['confirm_new_psw'] = $msg;
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
            'finder' => function($collection){
                return $collection->find($this->userId)->fetch();
            },
            'sanitizer' => function($posted){
                $psw = sha1(attr($posted, 'new_psw'));
                return [
                    '_password' => $psw,
                    '_oldPassword' => $psw
                ];
            },
            'onSuccess' => function ($user) {
                return $this->please->serve('user')->loginInstant([
                    'user' => $user,
                    'onSuccess' => function ($user) {
                        $message = 'Votre mot de passe a été mis à jour.';
                        
                        if( $this->please->isXHR() ){
                            return $this->resServ->JsonResponse([
                                'cuteModal' => $message,
                                'reload' => true
                            ]);
                        }
                        $this->please->setFlash('alert', '<i class="fa fa-check-circle"></i> '.$message);
                        return $this->redirect($this->please->getReferer());
                    }
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