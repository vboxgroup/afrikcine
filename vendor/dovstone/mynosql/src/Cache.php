<?php
namespace DovStone\MyNoSQL;

class Cache
{
    private $dir;

    public function __construct($dir = 'cache')
    {
        $this->dir = $dir;
    }
    
    public function set($id, $document)
    {
        file_put_contents($this->_cacheDoc($id), json_encode($document));
        return $document;
    }
    
    public function exists($id, $assoc = true)
    {
        $doc = $this->_cacheDoc($id);
        $cacheDoc = file_exists($doc);
        return $cacheDoc ? json_decode(file_get_contents($doc), $assoc) : false;
    }
    
    public function delete($id)
    {
        $doc = $this->_cacheDoc($id);
        if( $this->exists($id) ){
            unlink($doc);
            return true;
        }
        return false;
    }
    
    public function rmdir($dir = null)
    {
        $dir = dirname(dirname(__FILE__)) . '/'. ($dir ?? $this->dir);
        if(file_exists($dir)){
            $files = array_diff(scandir($dir), array('.','..'));
             foreach ($files as $file) {
               (is_dir("$dir/$file")) ? $this->rmdir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
        return false;
    }
    
    private function _cacheDoc($id)
    {
        $dir = dirname(dirname(__FILE__)) . '/' . $this->dir;

        if( !file_exists($dir) ){ mkdir($dir); }

        return $dir . '/' . $id . ".json";
    }
}