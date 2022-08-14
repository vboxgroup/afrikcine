<?php
namespace App\Twig;

use Twig\Markup;
use Twig\TwigFunction;
use App\Repository\UserRepository;
use App\Repository\BloggyRepository;
use Twig\Extension\AbstractExtension;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

class TwigExtension extends AbstractExtension
{
    public function __construct(PleaseService $please, BloggyRepository $bRepo, UserRepository $uRepo)
    {
        $this->please = $please;
        $this->bRepo = $bRepo;
        $this->uRepo = $uRepo;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getData', array($this, 'getData')),
            new TwigFunction('countBy', array($this, 'countBy')),
            new TwigFunction('getMedia', array($this, 'getMedia')),
            new TwigFunction('pop', array($this, 'pop')),
            //
            new TwigFunction('getFilmAndSerieFilteringContext', array($this, 'getFilmAndSerieFilteringContext')),
        );
    }
    
    public function pop($data = [])
    {
        if($data){
            $type = isset($data[0]) ? attr($data[0], 'type') : attr($data, 'type');
            if( $type ){
                return $this->bRepo->pop($data);
            }
            return $this->uRepo->pop($data);
        }
        return $data;
    }

    public function getData($type, $limit=4, $criteria=[], $orderBy=['createdAt'=>'desc'], $offset=null)
    {
        return $this->bRepo->getData($type, $limit, $criteria, $orderBy, $offset);
    }

    public function countBy($criteria=[])
    {
        return $this->bRepo->countBy($criteria);
    }

    public function getFilmAndSerieFilteringContext()
    {
        return $this->bRepo->getFilmAndSerieFilteringContext();
    }

    public function getMedia($item, $key = 'media')
    {
        $media = ['photos' => [], 'videos' => []];
        $defaultPreview = $this->please->serve('asset')->getLogoSrc(); 
        $defaultDesc = attr($item, 'title'); 
        $m = $this->please->serve('acf')->getAcf($item, $key);
        if($m){
            foreach($m as $k => $v){
                if( strpos($k, 'p') !== false ){
                    if($v!==''){
                        $i = str_replace('p', '', $k);
                        $media['photos'][] = [
                            'preview' => $v,
                            'description' => attr($m, 'd'.$i)
                        ];
                    }
                }
                if( strpos($k, 'v') !== false ){
                    if($v!==''){
                        $i = str_replace('v', '', $k);
                        $media['videos'][] = [
                            'ytID' => $v,
                            'preview' => attr($m, 'p'.$i, $defaultPreview),
                            'description' => attr($m, 'd'.$i)
                        ];
                    }
                }
            }
        }
        return $media;
    }
}
