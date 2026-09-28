<?php
namespace App\BLL;
use App\Core\ResponseHelper;
abstract class BaseBLL{
 protected function ok($m,$d=null){return ResponseHelper::success($m,$d);}
 protected function fail($m){throw new \App\Core\BusinessException($m);}
}
