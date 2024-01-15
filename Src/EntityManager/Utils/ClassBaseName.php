<?php

namespace Emma\ORM\EntityManager\Utils;


trait ClassBaseName
{
    public function baseName(string $className) 
    {
        $temp = explode("\\", $className);
        return $temp[count($temp) - 1];
    }
}