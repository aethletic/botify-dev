<?php

namespace Botify\Util;

class File
{
    public static function upload($path = false)
    {
        return $path ? new \CURLFile($path) : false;
    }
}
