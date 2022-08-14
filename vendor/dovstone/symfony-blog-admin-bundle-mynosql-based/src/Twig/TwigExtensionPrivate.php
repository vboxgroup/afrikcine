<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Twig;

use Symfony\Component\Filesystem\Filesystem;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Twig\Extension\AbstractExtension;
use ScssPhp\ScssPhp\Compiler;
use Twig\TwigFunction;
use Twig\Markup;

class TwigExtensionPrivate extends AbstractExtension
{
    public function __construct( PleaseService $please )
    {
        $this->please = $please;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('getAcf', array($this, 'getAcf')),
            new TwigFunction('getAcfRelatedTiles', array($this, 'getAcfRelatedTiles')),
            new TwigFunction('fetchEager', array($this, 'fetchEager')),
            new TwigFunction('getHits', array($this, 'getHits')),
            new TwigFunction('userCanHandleAcf', array($this, 'userCanHandleAcf')),
            new TwigFunction('getTh', array($this, 'getTh')),
            new TwigFunction('getTd', array($this, 'getTd')),
        );
    }

    public function getAcf()
    {
        return b()->findAllBy([['type','==','acf']])->fetch();
    }

    public function getAcfRelatedTiles($acf, $item=null)
    {
        return $this->please->serve('acf')->getAcfRelatedTiles($acf, $item);
    }

    public function fetchEager(?array $data = [], array $orderBy=['createdAt' => 'desc'])
    {
        return $this->please->getRepo('bloggy')->fetchEager($data, $orderBy);
    }

    public function getHits($uid)
    {
        return $this->please->getRepo('bloggy')->getHits($uid);
    }

    public function userCanHandleAcf($acf = [], $user = null)
    {
        return $this->please->serve('security')->userCanHandleAcf($acf, $user);
    }

    public function getTh($acfChildren)
    {
        return $this->getThAndTd($acfChildren, 0);
    }

    public function getTd($acfChildren, $item)
    {
        return $this->getThAndTd($acfChildren, 1, $item);
    }

    private function getThAndTd($acfChildren, $index, $item = null)
    {
        $timeServ = $this->please->serve('time');
        $t = '';
        foreach ($acfChildren as $needle => $columns) {

            if( in_array($needle, ['readListColumns', 'readListToggleActions']) )
            {
                preg_match_all("#([a-zA-Z]+\s*[a-zA-Z0-9,()_].*)#", $columns, $m, PREG_PATTERN_ORDER);
                if($m){
                    $columns = $m[0];
                    foreach ($columns as $c) {
                        $data = explode('@', $c);
                        if( in_array(count($data), [2, 3, 4, 5]) ){

                            $tag = $index == 0 ? 'th' : 'td';
                            $pathO = trim(preg_replace('/\s+/', '', $data[1]));
                            $path = explode('::', $pathO)[0];
                            $ternary = attr($item, trim($path), trim($data[3] ?? ''));

                            // lets retrieve dynamic document prop
                            if( strpos($pathO, '[') !== false ){
                                $xpl1 = explode('[', $pathO);
                                $xpl2 = explode(']', $xpl1[1]);
                                $prop = $xpl2[0] ?? '';
                                $collectionName = strtolower($xpl2[1] ?? 'b');
                                if( in_array($collectionName, ['b', 'u']) ){
                                    $document = $collectionName()->find(attr($item, $xpl1[0], 0))->fetch();
                                    $ternary = attr($document, trim($prop), trim($data[3] ?? '-'));
                                }
                            }

                            // checking ternary formats
                            $classData = explode('->', explode('::', $pathO)[1] ?? null);
                            if(is_countable($classData) && count($classData) === 2){
                                $service = $classData[0];
                                $meth = $classData[1];
                                if( $service == 'time' ){
                                    if($timeServ->isCorrectDateFormat($ternary)){
                                        if(method_exists($timeServ, $meth)){
                                            $ternary = $timeServ->$meth($ternary);
                                        }
                                    }
                                }
                                else {
                                    $ternary = $this->please->serve($service)->$meth($ternary);
                                }
                            }
                            
                            $val = $index == 0 ? $data[0] : $ternary;

                            if( $needle == 'readListColumns' ){
                                $cls = $tag == 'td' ? 'class="'.($data[4] ?? '-').'"' : '';
                                $t .= "<$tag title=\"$val\" data-minmax=".($data[2] ?? 130)." $cls>";
                                $t .= $val;
                            }
                            else {
                                if( $index == 0 ){
                                    $t .= "<$tag title=\"$val\" data-minmax=".($data[2] ?? 130).">";
                                    $t .= $val;
                                }
                                else {
                                    $path = trim(preg_replace('/\s+/', '', $data[2]));
                                    $val = attr($item, $path, $data[4] ?? 'off');
                                    $t .= "<$tag title=\"$val\" data-minmax=".($data[3] ?? 130).">";

                                    $t .= '<form 
                                                action="'. $this->please->serve('url')->getUrl("_admin/bloggy/{$item['id']}/basic-update") .'"
                                                type="post"
                                                data-js="bo={submit:submitBasicUpdate}"
                                            >
                                            <div class="pretty p-switch p-outline">
                                                <input
                                                    type="checkbox"
                                                    data-js="bo={click:toggleCheckboxOnOff}"
                                                    value="'.$val.'"
                                                    '.($val=='on'?'checked':'').'
                                                >
                                                <div class="state"><label>'.$data[1].'</label></div>
                                                <input type="hidden" name="'.$data[2].'" value="'.$val.'">
                                            </div>
                                        </form>';
                                }
                            }
                            $t .= "</$tag>";
                        }
                    }
                }
            }

        }
        return new Markup($t, 'UTF-8');
    }
}
