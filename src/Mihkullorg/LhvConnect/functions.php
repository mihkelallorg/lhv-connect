<?php

namespace Mihkullorg\LhvConnect;

/**
 * Taken from http://stackoverflow.com/a/4356295/5122070
 * 
 * @param int $length
 * @return string
 */
function generateMessageIdentification($length = 30)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}