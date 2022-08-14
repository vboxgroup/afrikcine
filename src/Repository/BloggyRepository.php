<?php

namespace App\Repository;

use App\Repository\UserRepository;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @method Bloggy|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bloggy|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bloggy[]    findAll()
 * @method Bloggy[]    findBy(array $criteria, array $orderBy = null, $this->please->getGlobal('limit') = null, $offset = null)
 */
class BloggyRepository
{
    public function __construct(PleaseService $please, UserRepository $uRepo)
    {
        $this->please = $please;
        $this->uRepo = $uRepo;
    }

    public function pop($items_ = [])
    {
        if($items_){
            if( isset($items_[0]) ){ $items = $items_; } else { $findOne = true; $items[] = $items_; }

            $acfServ = $this->please->serve('acf');

            foreach($items as $k => $item){
                if(isset($item['type'])){

                    $type = $item['type'];

                    switch ($type) {

                        case 'diapo':
                            $item['films'] = b()->findAllBy([['id','in', $this->iDs('film')],['id','in', attr($item, 'acf.props.films', [])]])->fetch();
                            //
                            if($item['films']){$items[$k] = $item;}
                            else { unset($items[$k]); }
                        break;

                        case 'flyer':
                            $items[$k] = array_merge([
                                'www' => b()->find($acfServ->getAcf($item, 'props.www'))->fetch()
                            ], $items[$k]);
                        break;

                        case 'film':
                        case 'serie':
                            $items[$k] = array_merge([
                                'genres' => b()->findAllBy([['id','in', attr($item, 'acf.autres_proprietes.genre')??[]]])->fetch(),
                                'saisons' => $this->getData('serie-saison', -1, [['acf.props.parent','==',$item['id']]], ['acf.props.numero_de_la_saison'=>'desc'])
                            ], $items[$k]);
                        break;

                        case 'emission':
                            $items[$k] = array_merge([
                                'category' => b()->find(attr($item, 'acf.props.parent'))->fetch()
                            ], $items[$k]);
                        break;

                        case 'serie-saison':
                            $items[$k] = array_merge([
                                'serie' => b()->find(attr($item, 'acf.props.parent'))->fetch(),
                                'episodes' => b()->findAllBy([['id', 'in', $this->iDs('serie-episode')],['acf.props.saison', '==', $item['id']]], ['acf.props.numero_episode'=>'desc'])->fetch()
                            ], $items[$k]);
                        break;

                        case 'emission-saison':
                            $items[$k] = array_merge([
                                'emission' => b()->find(attr($item, 'acf.props.parent'))->fetch(),
                                'episodes' => b()->findAllBy([['id', 'in', $this->iDs('emission-episode')],['acf.props.saison', '==', $item['id']]], ['acf.props.numero_episode'=>'desc'])->fetch()
                            ], $items[$k]);
                        break;

                        default:
                            # code...
                            break;
                    }

                    switch ($type) {

                        case 'film':
                        case 'serie-episode':
                            $items[$k] = array_merge([
                                'realisateurs' => u()->findAllBy([['id','in', attr($item, 'acf.realisateurs.listing')??[]]])->fetch(),
                                'acteurs' => u()->findAllBy([['id','in', attr($item, 'acf.acteurs_actrices.listing')??[]]], ['acf.acteurs_actrices.listing' => 'asc'])->fetch(),
                                'scenarios' => [
                                    'scenaristes' => u()->findAllBy([['id','in', attr($item, 'acf.scenarios.scenaristes')??[]]])->fetch(),
                                    'dialoguistes' => u()->findAllBy([['id','in', attr($item, 'acf.scenarios.dialoguistes')??[]]])->fetch(),
                                    'script_doctors' => u()->findAllBy([['id','in', attr($item, 'acf.scenarios.script_doctors')??[]]])->fetch(),
                                ],
                                'production' => [
                                    'producteurs' => u()->findAllBy([['id','in', attr($item, 'acf.production.producteurs')??[]]])->fetch(),
                                    'producteurs_executifs' => u()->findAllBy([['id','in', attr($item, 'acf.production.producteurs_executifs')??[]]])->fetch(),
                                    'producteurs_delegues' => u()->findAllBy([['id','in', attr($item, 'acf.production.producteurs_delegues')??[]]])->fetch(),
                                    'producteurs_associes' => u()->findAllBy([['id','in', attr($item, 'acf.production.producteurs_associes')??[]]])->fetch(),
                                ],
                                'equipe_technique' => [
                                    'accessoiristes' => $this->eTech($item,'accessoiristes'),
                                    'assistants_accessoiristes' => $this->eTech($item,'assistants_accessoiristes'),
                                    'operateurs' => $this->eTech($item,'operateurs'),
                                    'assistants_operateurs' => $this->eTech($item,'assistants_operateurs'),
                                    'assistants_realisateurs' => $this->eTech($item,'assistants_realisateurs'),
                                    'ingenieurs_son' => $this->eTech($item,'ingenieurs_son'),
                                    'assistants_ingenieurs_son' => $this->eTech($item,'assistants_ingenieurs_son'),
                                    'cameramen_cadreurs' => $this->eTech($item,'cameramen_cadreurs'),
                                    'costumiers' => $this->eTech($item,'costumiers'),
                                    'assistants_costumiers' => $this->eTech($item,'assistants_costumiers'),
                                    'electriciens' => $this->eTech($item,'electriciens'),
                                    'assistants_electriciens' => $this->eTech($item,'assistants_electriciens'),
                                    'machinistes' => $this->eTech($item,'machinistes'),
                                    'assistants_machinistes' => $this->eTech($item,'assistants_machinistes'),
                                    'maquilleurs' => $this->eTech($item,'maquilleurs'),
                                    'assistants_maquilleurs' => $this->eTech($item,'assistants_maquilleurs'),
                                    'photographes_de_plateau' => $this->eTech($item,'photographes_de_plateau'),
                                    'regisseurs' => $this->eTech($item,'regisseurs'),
                                    'assistants_regisseurs' => $this->eTech($item,'assistants_regisseurs'),
                                    'scripts' => $this->eTech($item,'scripts'),
                                    'cascadeurs' => $this->eTech($item,'cascadeurs'),
                                    'monteurs' => $this->eTech($item,'monteurs'),
                                    'assistants_monteurs' => $this->eTech($item,'assistants_monteurs'),
                                    'etalonneurs' => $this->eTech($item,'etalonneurs'),
                                    'compositeurs' => $this->eTech($item,'compositeurs'),
                                    'attaches_de_presse' => $this->eTech($item,'attaches_de_presse'),
                                    'auteurs' => $this->eTech($item,'auteurs'),
                                    'directeurs_de_casting' => $this->eTech($item,'directeurs_de_casting'),
                                    'consultants' => $this->eTech($item,'consultants'),
                                    'decorateurs' => $this->eTech($item,'decorateurs'),
                                    'assistants_decorateurs' => $this->eTech($item,'assistants_decorateurs'),
                                    'story_boarders' => $this->eTech($item,'story_boarders'),
                                ],
                                'societes' => [
                                    'distributeurs' => u()->findAllBy([['id','in', attr($item, 'acf.societes.distributeurs')??[]]])->fetch(),
                                    'producteurs_executifs' => u()->findAllBy([['id','in', attr($item, 'acf.societes.producteurs_executifs')??[]]])->fetch(),
                                    'diffuseurs' => u()->findAllBy([['id','in', attr($item, 'acf.societes.diffuseurs')??[]]])->fetch(),
                                    'co_production' => u()->findAllBy([['id','in', attr($item, 'acf.societes.co_production')??[]]])->fetch(),
                                ]
                            ], $items[$k]);
                        break;

                        case 'news':
                        case 'emission':
                            $items[$k] = array_merge([
                                'category' => b()->find(attr($item, 'acf.props.parent'))->fetch()
                            ], $items[$k]);

                            if($type == 'emission') {
                                $items[$k] = array_merge([
                                    'saisons' => $this->getData('serie-saison', -1, [['acf.props.parent','==',$item['id']]], ['acf.props.numero_de_la_saison'=>'desc'])
                                ], $items[$k]);
                            }
                        break;
                    }
                }
            }
            $items = array_values($items);
            return isset($findOne) && isset($items[0]) ? $items[0] : $items;
        }
        return $items_;
    }

    public function getData($type, $limit=4, $criteria=[], $orderBy=['createdAt'=>'desc'], $offset=null)
    {
        switch ($type) {
            
            case 'films-a-venir':
                $today = (new \DateTime())->format('Y-m-d');
                $c = [
                    ['id','in',$this->iDs('film')],
                    ['acf.dates.date_de_sortie','>',$today],
                    ['acf.realisateurs.listing','exists in',$this->iDs('users')]
                ];
            break;
            
            case 'dernieres-bandes-annonces':
                $c = [
                    ['id','in',array_merge($this->iDs('film'), $this->iDs('serie'))],
                    ['acf.videos.ba','!=',''],
                ];
            break;
            
            case 'film':
            case 'serie':
            case 'news':
            case 'emission':
            case 'flyer':
            case 'categorie':
            case 'serie-saison':
            case 'serie-episode':
                $c = [['id','in',$this->iDs($type)]];
            break;

            default:
                $c = [];
            break;
        }

        $criteria = array_merge($c, $criteria);

        return [
            'page' => $this->please->getRequestStackQuery()->get('page', 1),
            'limit' => $limit,
            'count' => b()->countBy($criteria)->fetch(),
            'items' => $this->pop(
                is_null($offset)
                ? b()->findBy($criteria)->orderBy($orderBy)->limit($limit)->fetch()
                : b()->findBy($criteria)->orderBy($orderBy)->limit($limit)->offset($offset)->fetch()
            )
        ];
    }

    public function countBy($criteria=[])
    {
        return b()->countBy($criteria)->fetch();
    }

    private function iDs($name)
    {
        switch ($name) {
            
            case 'film':
            case 'serie':
            case 'categorie':
            case 'page':
            case 'serie-saison':
            case 'serie-episode':
            case 'emission-saison':
            case 'emission-episode':
                $this->please->setStorage([[$name, function() use ($name) {
                    return b()->findIDs([
                        ['type','==',$name],
                        ['published','==','on']
                    ])->fetch();
                }]]);
                return $this->please->getStorage($name);
            break;

            case 'news':
            case 'emission':
                $this->please->setStorage([[$name, function() use ($name) {
                    return b()->findIDs([
                        ['type','==',$name],
                        ['published','==','on'],
                        ['acf.props.parent','in',$this->iDs('categorie')]
                    ])->fetch();
                }]]);
                return $this->please->getStorage($name);
            break;

            case 'flyer':
                $this->please->setStorage([[$name, function() use ($name) {
                    return b()->findIDs([
                        ['type','==',$name],
                        ['published','==','on'],
                        ['acf.props.www','in', array_merge(
                            $this->iDs('film'), 
                            $this->iDs('serie'), 
                            $this->iDs('news'), 
                            $this->iDs('emission'), 
                            $this->iDs('categorie'), 
                            $this->iDs('page')
                        )],
                    ])->fetch();
                }]]);
                return $this->please->getStorage($name);
            break;

            case 'users':
                $this->please->setStorage([[$name, function() use ($name) {
                    return u()->findIDs([
                        ['enabled','==','on']
                    ])->fetch();
                }]]);
                return $this->please->getStorage($name);
            break;

            default:
                return [];
            break;
        }
    }

    public function getFilmAndSerieFilteringContext()
    {
        $q = $this->please->getRequestStackQuery();
        if( !is_null($q->get('filtrer')) ){
            $orderBy = [];
            $c = [];
            //
            $o = $q->get('o'); // asc || desc
            $a = $q->get('a'); // annee de production
            $g = $q->get('g'); // genre
            //
            if($o){$orderBy = ['title' => in_array(strtolower($o), ['asc','desc']) ? $o : 'asc'];}
            if($a){
                $a = explode('-', $a);
                $a1 = (int)trim($a[0] ?? null);
                $a2 = (int)trim($a[1] ?? null);
                if($a1 && $a2){
                    $c = array_merge([
                        ['acf.dates.annee_de_production', '>=', $a1],
                        ['acf.dates.annee_de_production', '<=', $a2]
                    ], $c);
                }
            }
            if($g){$c = array_merge([['acf.autres_proprietes.genre', 'exists in', [$g]]], $c);}
            //
            return [
                'criteria' => $c,
                'orderBy' => $orderBy
            ];
        }
        return [
            'criteria' => [],
            'orderBy' => ['createdAt'=>'desc']
        ];
    }

    private function eTech($item, $key)
    {
        return u()->findAllBy([['id','in', attr($item, "acf.equipe_technique.$key")??[]]])->fetch();
    }
}