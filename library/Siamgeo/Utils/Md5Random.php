<?php
class Siamgeo_Utils_Md5Random
{
    #Generate a random key from /dev/random
    private static function getKey($length = 128){
        $fp = @fopen('/dev/urandom','rb');
        if ($fp !== FALSE) {
            $key = substr(base64_encode(@fread($fp,($length + 7) / 8)), 0, (($length + 5) / 6)  - 2);
            @fclose($fp);
            return $key;
        }

        return null;
    }

    public static function randomMd5String()
    {
        return md5(self::getKey());
    }
}
