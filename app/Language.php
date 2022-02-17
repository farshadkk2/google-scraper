<?php


namespace App;

class Language
{
    public $code;
    public $name;

    function __construct($code = "en", $name = "")
    {
        $this->code = $code;
        $this->name = $name;
    }
}
