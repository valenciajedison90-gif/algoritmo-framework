<?php
namespace App\Core;
class ResponseHelper{
 public static function success($m,$d=null){return ["estado"=>true,"mensaje"=>$m,"datos"=>$d];}
 public static function error($m){return ["estado"=>false,"mensaje"=>$m];}
}
