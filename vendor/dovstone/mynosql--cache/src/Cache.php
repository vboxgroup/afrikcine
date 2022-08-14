<?php
namespace DovStone\MyNoSQL;

class Cache
{
    private $cacheDir;

    public function __construct()
    {
        $this->cacheDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        if(!file_exists($this->cacheDir)){
            mkdir($this->cacheDir);
        }
    }

    public function exists($uid)
    {
        return file_exists($this->getFilename($uid));
    }

    public function get($uid)
    {
        $filename = $this->getFilename($uid);
        if( file_exists($filename) ){
            return json_decode(file_get_contents($filename), true);
        }
        return [];
    }

    public function set($uid, $content)
    {
        $filename = $this->getFilename($uid);
        file_put_contents($filename, json_encode($content));
        return $this->get($uid);
    }

    public function delete($uid)
    {
        if($this->exists($uid)){
            unlink($this->getFilename($uid));
        }
        return false;
    }

    public function getFilename($uid)
    {
        return $this->cacheDir . $uid . '.json';
    }
}