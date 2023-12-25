<?php

namespace Nelwhix\WhatsappPhpClient\utils;

class Utils
{
 public static function dd(...$args) {
     var_dump("how far");
     foreach ($args as $var) {
//         "how far";
         var_dump($var);
     }

     die(5);
 }

 public static function generateUint32(): int
 {
     return mt_rand(0, 0xFFFFFFFF);
 }
}