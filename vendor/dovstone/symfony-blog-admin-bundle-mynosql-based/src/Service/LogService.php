<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogService extends AbstractController
{
    private $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function put($level, $message, $data = null)
    {
        /*
        $appendMsg = $prependMsg = $acf = '';

        if( $user = $this->please->getUser() ){
            $appendMsg =  '<a href="'. $this->generateUrl('_updateUser', ['id' => $user['id'] ]) .'"><b>' . $this->please->serve('user')->getFullName($user) . ' (<span class="mle">'. $user['_mle'] .'</span>)</b></a> : ';
        }

        if( isset($data['type']) && $data['type'] != '' ){
            if( strpos($message, 'suppression') == false ){
                $type = $data['type'];
                $prependMsg =  ' de '.($type == 'acf' ? "l'ACF " : '').'<a href="'. $this->generateUrl('_updateBloggy', [
                    'id' => $data['id'],
                    'type' => $type
                ]) .'">' . $data['title'] . '</a>';
            }
            else {
                $prependMsg =  ' de ' . $data['title'];
            }
        }

        /*
        $data = b()->insert([
            'type' => 'log',
            '_message' => $appendMsg . $message . $prependMsg,
            '_level' => $level,
            'createdAt' => (new \DateTime())->format("Y-m-d H:i:s")
        ]);
        */

        //$this->please->serve('crud')->deleteCaches();

        return $data;
    }
}
