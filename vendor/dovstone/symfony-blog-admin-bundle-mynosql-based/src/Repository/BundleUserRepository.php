<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Repository;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @method Bloggy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bloggy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bloggy[]    findAll()
 * @method Bloggy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BundleUserRepository
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->bloggyRepo = $this->please->getRepo('bloggy');
        $this->bloggyStore = $this->please->getMyNoSQLCollection('bloggies');
    }

    public function fetchEager($data = [], $orderBy=['createdAt' => 'desc'])
    {
        return $data;
        if( isset($data[0]) ){ $data_ = $data; } else { $data_[] = $data; }
        
        $ids = [];

        if( $data_ ){

            foreach($data_ as $d){ $ids[] = attr($d, 'id'); }
            
            $db = $this->please->getMyNoSQLCollection('users');

            $rows = $db->findAllBy([['id','in', $ids]], $orderBy)->fetch();

            if( $rows ){
                foreach ($rows as $k => $item) {

                    //
                    $parentID = (int)attr($item, 'parent');
                    $rows[$k]['parent'] = $parentID ? $db->find($parentID)->fetch() : null;

                    //
                    $rows[$k]['_bloggies'] = $db->findAllBy([
                        ['id','==',attr($user, 'id')]
                    ], ['createdAt' => 'desc'])->fetch() ?? [];
                }
            }

            return isset($data[0]) ? $rows : (isset($rows[0]) ? $rows[0] : []);
        }
        return $data;
    }
}