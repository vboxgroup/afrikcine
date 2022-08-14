<?php

namespace App\Repository;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @method Bloggy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bloggy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bloggy[]    findAll()
 * @method Bloggy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }
    
    public function pop($items_ = [])
    {
        if($items_){
            if( isset($items_[0]) ){ $items = $items_; } else { $findOne = true; $items[] = $items_; }
            foreach($items as $k => $item){
                $items[$k] = array_merge([

                    'infos' => b()->findOneBy([
                        ['type','==','info-sup-user'],
                        ['acf.props.user','==',$item['id']],
                    ])->fetch(),

                    /*'films' => b()->findAllBy([
                        ['type','==','film'],
                        ['acf.equipe_artistique.acteurs','contains',$item['id']],
                    ])->fetch(),

                    'series' => b()->findAllBy([
                        ['type','==','serie'],
                        ['acf.equipe_artistique.acteurs','contains',$item['id']],
                    ])->fetch(),

                    'news' => b()->findAllBy([
                        ['type','==','news'],
                        ['acf.related.stars','contains',$item['id']],
                    ])->fetch()*/


                ], $items[$k]);
            }
            $items = array_values($items);
            return isset($findOne) && isset($items[0]) ? $items[0] : $items;
        }
        return $items_;
    }
}