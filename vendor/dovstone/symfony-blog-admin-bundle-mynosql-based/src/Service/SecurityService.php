<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/*
// stores an attribute in the session for later reuse
    $this->session->set('attribute-name', 'attribute-value');

    // gets an attribute by name
    $foo = $this->session->get('foo');

    // the second argument is the value returned when the attribute doesn't exist
    $filters = $this->session->get('filters', []);
*/

class SecurityService extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session, PleaseService $please)
    {
        $this->please = $please;
        $this->session = $session;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getUser()
    {
        return $this->getFreshUser();
    }

    public function __isGranted($params)
    {
        $byUserId = false;
        $byUserRoles = false;

        if( is_array($params['isGranted']) && isset($params['isGranted']['byUserId']) ){
            $byUserIdIsRequired = true;
            $byUserId = $this->_isGrantedById($params['isGranted']['byUserId']);
        }
        if( is_array($params['isGranted']) && isset($params['isGranted']['byUserRoles']) && is_array($params['isGranted']['byUserRoles']) ){
            $byUserRolesIsRequired = true;
            $byUserRoles = $this->_isGrantedByRoles($params['isGranted']['byUserRoles']);
        }

        if(
            // no restriction
            (is_null($params['isGranted']) || empty($params['isGranted']))
            ||
            // restriction by id
            (isset($byUserIdIsRequired) && $byUserId)
            ||
            // restriction by role
            (isset($byUserRolesIsRequired) && $byUserRoles)
        ){
            return true;
        }

        return false;
    }
    
    public function userCan($role = 'admin', $strictRole = true, $user = null)
    {
        // if( !in_array($role, ['admin', 'edit', 'editor', 'moderate', 'moderator']) ){
        //     return false;
        // }
        if($user = $this->getFreshUser($user ?? $this->please->getUser())){
            
            $userRoles = attr($user, 'roles', []);

            if($strictRole){
                if( in_array($role, $userRoles) ){
                    return true;
                }
                return false;
            }
            if(
                in_array($role, $userRoles)
                ||
                (in_array($role, ['edit', 'editor']) && in_array('editor', $userRoles))
                ||
                (in_array($role, ['moderate', 'moderator']) && in_array('moderator', $userRoles))
                ||
                (in_array($role, ['admin', 'administrator']) && in_array('administrator', $userRoles))
                ||
                (in_array('admin', $userRoles))
            ){
                return true;
            }
        }
        return false;
    }
    
    public function userIs($role = 'admin', $strictRole = true, $user = null)
    {
        return $this->userCan($role, $user, $strictRole);
    }
    
    public function userCanHandleAcf($acf = [], $user = null)
    {
        if($user = $this->getFreshUser($user)){
            $userAcfsIds = attr($user, 'acfToHandle', []);
            if( is_string($acf) ){
                $acf = b()->findOneBy([
                    ['type','==','acf'],
                    ['name','==',$acf],
                ])->fetch();
            }
            foreach ($userAcfsIds as $acfId) {
                if( $acfId == attr($acf, 'id') ){
                    return true;
                }
            }
        }
        return false;
    }

    public function getFreshUser( $user = null )
    {
        $user = $user ?? $this->session->get('User');
        $userId = attr($user, 'id', -1);
        $user = $this->please->getMyNoSQL()->collection('users')->find($userId)->fetch();
        return empty($user) ? null : $user;
    }

    private function _isGrantedByRoles($byUserRoles)
    {
        $ROLES = [];
        foreach ($byUserRoles as $role) {
            if( $role === '__ANY__' ) {
                return $this->getUser() !== null;
            }
            else if( $role === '__NONE__' ||  $role === '__ANON__' ||  $role === '__ANONYMOUS__' ) {
                return $this->getUser() === null;
            }
            else {
                //$ROLES[] = 'ROLE_' . trim(strtoupper($role));
                //$ROLES[] = trim(strtoupper($role));
                $ROLES[] = trim($role);
            }
        }
        return $this->_loopOverRoles($ROLES);
    }

    private function _isGrantedById($byUserId)
    {
        $userId = attr($this->getUser(), 'id');
        if( (is_int($byUserId) || is_string($byUserId)) && (int)$userId === (int)$byUserId && $byUserId !== 0 ){
            return true;
        }
        return false;
    }

    private function _loopOverRoles(array $roles)
    {
        $userRoles = attr($this->getUser(), 'roles', []);
        if($userRoles && is_array($userRoles)){
            foreach ($userRoles as $userRole) {
                if(in_array($userRole, $roles) ){
                    return true;
                }
            }
        }
        return false;

        /*$userRoles = json_decode(attr($this->getUser(), '_roles', null));
        if($userRoles){
            foreach($userRoles as $userRole){
                if( in_array(strtoupper($userRole), $roles) ){
                    return true;
                }
            }
        }
        return false;*/
    }
}