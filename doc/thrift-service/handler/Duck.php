<?php
namespace handler;

class Duck implements \tutorial\DuckIf
{

    public function cry($str)
    {
        error_log("cry($str)");
        return $str;
    }

}
