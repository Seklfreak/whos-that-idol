<?php

namespace WhosThatIdolBundle\Utils;


class Base64ApiSafe
{
    public static function base64apisafe_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64apisafe_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

}