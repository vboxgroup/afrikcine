<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Controller;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use ScssPhp\ScssPhp\Compiler;

/**
 * @Route("_admin/reset/")
 */
class BundleResetController extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
    }

    /**
     * @Route("{type}", name="_resetByType")
     */
    public function _resetByType($type)
    {
        return $this->please->serve('crud')->readList([
            'collection' => 'bloggies',
            'finderCollection' => function($collection) use ($type) {
                return [
                    [ 'type','==', $type ]
                ];
            },
            'perPage' => 10000000,
            'view' => function($items){
                foreach ($items as $item) {
                    $this->please->serve('crud')->delete([
                        'collection' => 'bloggies',
                        'log' => false,
                        'finder' => function() use ($item) {
                            return $item;
                        }
                    ]);
                }; 
                return new JsonResponse([
                    'gToast' => 'Données supprimées avec succès.'
                ]);
            }
        ]);
    }
}
