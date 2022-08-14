<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Repository;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @method Bloggy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bloggy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bloggy[]    findAll()
 * @method Bloggy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BundleBloggyRepository
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function fetchEager($items_ = [])
    {
        if($items_){
            if( isset($items_[0]) ){ $items = $items_; } else { $findOne = true; $items[] = $items_; }
        
            foreach($items as $k => $item){
                //
                $parentID = (int)attr($item, 'parent');
                $items[$k]['parent'] = $parentID ? b()->find($parentID)->fetch() : null;

                //
                $items[$k]['children'] = b()->findAllBy([
                    ['parent','==',attr($item, 'id')],
                    ['published','==','on'],
                    ['type','!==','acf'],
                    ['inMenu','in',['off', 'no']],
                ], ['createdAt' => 'desc'])->fetch() ?? [];

                //
                $items[$k]['childrenInMenu'] = b()->findAllBy([
                    ['parent','==',attr($item, 'id')],
                    ['published','==','on'],
                    ['type','!==','acf'],
                    ['inMenu','in',['on', 'yes']],
                ], ['createdAt' => 'desc'])->fetch() ?? [];

                //
                /*$items[$k]['_articles'] = b()->findAllBy([
                    ['parent','==',attr($item, 'id')],
                    ['published','==','on'],
                    ['type','!==','acf'],
                    ['linkType','==','article'],
                    ['inMenu','!==','on'],
                ], ['createdAt' => 'desc'])->fetch() ?? [];*/
            }
            $items = array_values($items);
            return isset($findOne) && isset($items[0]) ? $items[0] : $items;
        }
        return $items_;
    }

    public function findAcf(string $type, int $limit = -1, array $orderBy = ['createdAt' => 'desc'], int $offset = 0)
    {
        $items = b()->findBy([
            ['type','==',$type],
            ['published','==','on']
        ], $orderBy, $limit)->offset($offset)->fetch();

        if( $items ){

            if( !isset($items[0]) ){
                $items = [$items];
            }

            foreach ($items as $item) {
                $parentID = (int)attr($item, 'parent');
                $item['parent'] = $parentID ? b()->find($parentID)->fetch() : null;
            }
        }

        return $items && $limit == 1 && isset($items[0]) ? $items[0] : $items;
    }

    public function getHits($uid)
    {
        if($uid){
            if (is_array($uid)){$uid = attr($uid, 'id');}
            $row = $this->please->getMyNoSQL()->getPDO()->query("SELECT val FROM `hits` WHERE (uid = $uid)")->fetch(\PDO::FETCH_ASSOC);
            $hits = (int) ($row['val'] ?? 0);
            return $hits;
        }
        return 0;
    }
}