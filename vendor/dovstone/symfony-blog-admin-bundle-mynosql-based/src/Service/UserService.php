<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserService extends AbstractController
{

    public function __construct(SessionInterface $session, PleaseService $please)
    {
        $this->please = $please;
        $this->session = $session;
        $this->User = $this->please->serve('security')->getUser();
    }
    
    public function login($params)
    {
        //Required to create <input type="hidden" name="login_token" />
        //on your form
        $params = array_merge([
            'ifNotXHR' => null,
            'onAlreadyLoggedIn' => null,
            'isValid' => null,
            'validator' => [],
            'finder' => function ($posted) {},
            'formView' => function ($userWasNotFound = null, $params) {}, // will be TRUE if user was not found
            'onSuccess' => function ($found, $params) {},
        ], $params);
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        //forcing
        $params['isGranted'] = [
            "byUserRoles" => ['__NONE__']
        ];

        if( $this->User ){
            if( is_callable($params['onAlreadyLoggedIn']) ){
                return $params['onAlreadyLoggedIn']($this->User);
            }
            else {
                return $this->please->serve('execute_before')->defaultFallback('onAlreadyLoggedIn');
            }
        }

        if ($this->please->getRequest()->isMethod('POST')) {
            return $this->please->serve('crud')->checkDataValidaty($params, function ($params) {

                if( is_null($params['isValid']) || !empty($params['validator']) ){

                    $all = $this->please->getRequest()->request->all();

                    $collection = $this->please->getMyNoSQLCollection('users');

                    $found = $params['finder']($all, $collection);
                    
                    if( !$found ){
                        $this->please->serve('log')->put('warning', "<b>".attr($all, 'ident')."</b> : Tentative échouée de connexion");
                        return $params['formView'](true, $params);
                    }
                    // lets check _stayConnected
                    if( $stayConnected = $this->please->getRequestStackRequest()->get('_stayConnected') ){
                        $this->please->serve('crud')->basicUpdate([
                            'collection' => 'users',
                            'finder' => function() use ($found) {
                                return $found;
                            },
                            'sanitizer' => function() use ($stayConnected) {
                                return ['_stayConnected' => $stayConnected];
                            }
                        ]);
                    }
                    $this->please->serve('security')->getSession()->set('User', $found);

                    $this->please->serve('log')->put('success', 'Connexion réussie');

                    return $params['onSuccess']($found, $params);
                }
                else {

                    $this->please->serve('log')->put('warning', "<b>".attr($all, 'ident')."</b> : Tentative échouée de connexion");

                    return $params['formView'](true, $params);
                }
            });
        }
        return $params['formView'](null, $params);
    }

    public function loginInstant($params)
    {
        $params = array_merge([
            'ifNotXHR' => null,
            'user' => null,
            'onSuccess' => function ($user) {}
        ], $params);
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        $user = $params['user'];

        $this->please->serve('security')->getSession()->set('User', $user);

        return $params['onSuccess']($user);
    }

    public function logout($params)
    {
        $params = array_merge([
            'ifNotXHR' => null,
            'onAlreadyLoggedOut' => function(){},
            'onSuccess' => function($cachedUser){},
        ], $params);

        if( is_string($params['ifNotXHR']) ){
            if( !$this->isXHR() ) {
              return $this->XHRReturn($params);
            }
        }

        if( !$this->User ){
            return $params['onAlreadyLoggedOut']();
        }

        $cachedUser = $this->User;

        $this->please->serve('log')->put('info', 'Déconnexion réussie');

        $this->please->serve('security')->getSession()->set('User', null);
        
        return $params['onSuccess']($cachedUser);
    }

    public function reLogin($params)
    {
        $params = array_merge([
            'user' => null,
            'onSuccess' => function ($cachedUser) {},
        ], $params);

        return $this->logout([
            'onSuccess' => function($cachedUser) use ($params){
                return $this->loginInstant([
                    'user' => $params['user'] ?? $cachedUser,
                    'onSuccess' => function () use ($params, $cachedUser) {
                        return $params['onSuccess']($cachedUser);
                    },
                ]);
            }
        ]);
    }

    public function forgot($params)
    {
        $params = array_merge([
            'isValid' => null,
            'validator' => [],
            'onNotFound' => function($emailNotFound = null){}, // will be TRUE if user was not found
            'finder' => function ($posted, $collection){},
            'formView' => function (){},
            'onFound' => function ($user) {},
        ], $params);

        //forcing
        $params['isGranted'] = ["byUserRoles" => ['__NONE__']];

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $params['_csrfToken'] = sha1(uniqid());
        
        if ($this->please->getRequest()->isMethod('POST')) {

            return $this->please->serve('crud')->checkDataValidaty($params, function ($params)  {

                if( is_null($params['isValid']) || !empty($params['validator']) ){
                    
                    $collection = $this->please->getMyNoSQLCollection('users');

                    $user = $params['finder']($this->please->getRequest()->request->all(), $collection);
                    if( !$user ){
                        return $params['onNotFound']();
                    }
                    return $params['onFound']($user);
                }
                return $params['formView']();
            });
        }

        return $params['formView']();
    }

    private function outGoingEmailConfirmation($params)
    {

        $params = array_merge([
            'subject' => "Validation de compte",
            'subscriber' => null,
            'inComingEmailConfirmationRouteName' => 'inComingEmailConfirmation',
            'body' => function($mail, $subscriber, $link, $token, $mediaService, $urlService){},
            'onSuccess' => function($subscriber, $reSendLink, $validationLink){},
            'onError' => function($e){}
        ], $params);

        $params['isGranted'] = [
            "byUserRoles" => ['__NONE__']
        ];

        if(false === $this->__isGranted($params)){
            if(is_callable($otherwise = $this->_otherwise($params))){
                //devloper otherwise
                return $otherwise();
            }
            else {
                //default otherwise
                return $this->_defaultFallback('otherwise');
            }
        }

        return $this->basicEdit([
            'finder' => function () use ($params) {
                return $params['subscriber'];
            },
            'sanitizer' => function ($handled) {
                $validationToken = sha1(uniqid());
                $handled->setValidationToken($validationToken);
                return $handled;
            },
            'onSuccess' => function ($subscriber) use ($params) {

                $envS = $this->getBundleService('env');

                $token = $subscriber->getValidationToken();
                $subscriberEmail = $subscriber->getEmail();
                $validationLink = trim($envS->getAppEnv('APP_ORIGIN'), '/') . $this->generateUrl($params['inComingEmailConfirmationRouteName'], ['token' => $token]);
                $reSendLink = $this->generateUrl($this->request->attributes->get('_route'), ['email' => $subscriberEmail]);


                $MAILER = explode('|', $envS->getAppEnv('MAILER'));
                $username = $MAILER[0];

                return $this->sendEmail([
                    'subject' => $params['subject'],
                    'from' => [$username => $envS->getAppEnv('APP_NAME')],
                    'to' => [$subscriberEmail => $this->getUserFullName($subscriber)],
                    'body' => function( $mail, $mediaService, $urlService ) use ($params, $subscriber, $validationLink, $token) {
                        return $params['body']( $mail, $subscriber, $validationLink, $mediaService, $urlService, $token );
                    },
                    'onSuccess' => function() use ($params, $subscriber, $reSendLink, $validationLink, $token) {
                        return $params['onSuccess']($subscriber, $reSendLink, $validationLink, $token);
                    },
                    'onError' => function($e){
                        return $params['onError']($e);
                    }
                ]);
            }
        ]);
    }

    private function outGoingEmailRetrieveAccount($params)
    {
        //Required to create <input type="hidden" name="forgot_token" />
        //into your form
        $params = array_merge([
            'subject' => "Récupération de compte",
            'from' => ['me@domain.com'],
            'user' => null,
            'inComingEmailRetrieveAccountRouteName' => 'inComingEmailRetrieveAccount',
            'body' => function($mail, $user, $link, $mediaService, $urlService){},
            'onError' => function($params){},
            'onSuccess' => function($user, $reSendLink, $token){},
        ], $params);

        //forcing
        $params['isGranted'] = [
            "byUserRoles" => ['__NONE__']
        ];

        return $this->basicEdit([
            'finder' => function () use ($params) {
                return $params['user'];
            },
            'sanitizer' => function ($handled) {
                $forgotToken = sha1(uniqid());
                $handled->setForgotToken($forgotToken);
                return $handled;
            },
            'onSuccess' => function ($user) use ($params) {
                $token = $user->getForgotToken();
                $userEmail = $user->getEmail();
                $retrieveLink = trim($this->getBundleService('env')->getAppEnv('APP_ORIGIN'), '/') . $this->generateUrl($params['inComingEmailRetrieveAccountRouteName'], ['token' => $token]);
                $reSendLink = $this->generateUrl($this->request->attributes->get('_route'), ['id' => $user->getId()]);

                $envS = $this->getBundleService('env');
                $MAILER = explode('|', $envS->getAppEnv('MAILER'));
                $username = $MAILER[0];

                return $this->sendEmail([
                    'subject' => $params['subject'],
                    'from' => [$username => $envS->getAppEnv('APP_NAME')],
                    'to' => [$userEmail => $this->getUserFullName($user)],
                    'body' => function($mail, $mediaService, $urlService) use ($params, $user, $retrieveLink) {
                        return $params['body']( $mail, $user, $retrieveLink, $mediaService, $urlService );
                    },
                    'onSuccess' => function() use ($params, $user, $reSendLink, $token, $retrieveLink) {
                        return $params['onSuccess']($user, $reSendLink, $token, $retrieveLink);
                    },
                    'onError' => function($e){
                        return $params['onError']($e);
                    }
                ]);
            }
        ]);
    }

    public function getUserFullName( $user = null )
    {
        $u = $user ?? $this->User;
        $fullname = trim(attr($u, 'lastname') . ' ' . attr($u, 'firstname'));
        return empty($fullname) ? ($user['username'] ?? '--') : $fullname;
    }

    private function _otherwise($params)
    {
        if( is_array($params['isGranted']) && isset($params['isGranted']['otherwise']) && is_callable($params['isGranted']['otherwise']) ){
            return $params['isGranted']['otherwise'];
        }
        return false;
    }

}