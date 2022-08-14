<?php
namespace DovStone\MyNoSQL;

class Exception extends \Exception
{
    public function __construct($exception)
    {
        dump("Exception: $exception");
    }
}