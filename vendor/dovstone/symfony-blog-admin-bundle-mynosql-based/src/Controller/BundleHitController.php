<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Controller;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use ScssPhp\ScssPhp\Compiler;

/**
 * @Route("_admin/hits/")
 */
class BundleHitController extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
    }

    /**
     * @Route("set", name="_setCurrentPostHits")
     */
    public function _setCurrentPostHits()
    {
        if( $this->please->getRequestStackQuery()->get('bo') == 'true' ){
            $val = 0;
        }
        else {
            try {
                $uid = $this->please->getPost()['id'] ?? null;
                $val = ($this->please->getRepo('bloggy')->getHits($uid)); // query the current hits
                if( $val == 0 ){
                    $val = 1;
                    // insert
                    $sql = "INSERT INTO `hits` (uid, val) VALUE (?, ?)";
                    $params = [$uid, $val];
                }
                else {
                    // update
                    $val = $val+1;
                    $sql = "UPDATE `hits` SET val=? WHERE uid=?";
                    $params = [$val, $uid];
                }
                h()->commit($sql, $params);
                //
            } catch (\Throwable $th) {
                $val = 0;
            }
        }
        return new JsonResponse(['hits' => $val]);
    }

    /**
     * @Route("{id}/set", name="_setHits")
     */
    public function _setHits($id)
    {
        $post = b()->find($id)->fetch();
        return $this->_setCurrentPostHits($post['id']);
    }

    /**
     * @Route("reset", name="_resetHits")
     */
    public function _resetHits()
    {
        h()->commit("TRUNCATE `hits`");
        return new JsonResponse(['hits reset' => true]);
    }
}
