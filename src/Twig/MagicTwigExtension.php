<?php
namespace App\Twig;

use Twig\Markup;
use Twig\TwigFunction;
use App\Repository\UserRepository;
use App\Repository\BloggyRepository;
use Twig\Extension\AbstractExtension;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

class MagicTwigExtension extends AbstractExtension
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
            new TwigFunction('getProdYears', array($this, 'getProdYears')),
        );
    }
    
    public function getProdYears($limit=1800, $step=5)
    {
        $today = (int)(new \DateTime())->format('Y');
        $years = [];
        $yearsSteps = [];
        for ($y=$today; $y >= $limit; $y--) {$years[] = $y;}
        foreach($years as $i => $y){
            $i = $i * $step;
            $arr = array_slice($years, $i, $step);
            if($arr){
                if(count($arr)<$step){
                    $lastVal = end($arr);
                    $arr[] = $lastVal - $step;
                }
                $yearsSteps[] = $arr;
            }
        }
        $final = [];
        if($yearsSteps){
            foreach ($yearsSteps as $years) {
                $final[] = end($years)." - $years[0]";
            }
        }
        return $final;
    }
}
