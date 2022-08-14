<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Twig;

use Symfony\Component\Filesystem\Filesystem;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Twig\Extension\AbstractExtension;
use ScssPhp\ScssPhp\Compiler;
use Twig\TwigFunction;
use Twig\Markup;

class PHPNativeExtension extends AbstractExtension
{
    public function __construct( PleaseService $please )
    {
        $this->please = $please;
        $this->container = $this->please->getContainer();
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('jsonDecode', array($this, 'jsonDecode')),
            new TwigFunction('arrayfy', array($this, 'arrayfy')),
            new TwigFunction('intfy', array($this, 'intfy')),
            new TwigFunction('floatfy', array($this, 'floatfy')),
            new TwigFunction('md5', array($this, 'md5')),
            new TwigFunction('sha1', array($this, 'sha1')),
            new TwigFunction('strpos', array($this, 'strpos')),
            new TwigFunction('substr', array($this, 'substr')),
            new TwigFunction('is_string', array($this, 'is_string')),
            new TwigFunction('array_merge_values', array($this, 'array_merge_values')),
            new TwigFunction('arr_merge_val', array($this, 'arr_merge_val')),
            new TwigFunction('ceil', array($this, 'ceil')),
            new TwigFunction('array_fill', array($this, 'array_fill')),
        );
    }

    public function jsonDecode($value, $arrayfy = null)
    {
        return json_decode($value, $arrayfy);
    }

    public function arrayfy($data)
    {
        return (array)$data;
    }

    public function intfy($val)
    {
        return (int)$val;
    }

    public function floatfy($val)
    {
        return (float)$val;
    }
    
    public function md5($value = null)
    {
        return md5($value);
    }
    
    public function sha1($value = null)
    {
        return sha1($value);
    }

    public function strpos($haystack, $needle)
    {
        return strpos($haystack, $needle);
    }

    public function substr($string, $start=0, $length=null)
    {
        return substr($this->please->serve('string')->getAccentsLess($string), $start, $length);
    }

    public function is_string($var)
    {
        return is_string($var);
    }
    
    public function array_merge_values($arrays)
    {
        $merged = [];
        if($arrays){
            foreach ($arrays as $array) {
                if($array){
                    foreach ($array as $arr) {
                        $merged[] = $arr;
                    }
                }
            }
        }
        return $merged;
    }
    
    public function arr_merge_val($arrays)
    {
        return $this->array_merge_values($arrays);
    }
    
    public function ceil($value)
    {
        return ceil($value);
    }
    
    public function array_fill(int $start_index, int $num, $value)
    {
        return array_fill($start_index, $num, $value);
    }
}
