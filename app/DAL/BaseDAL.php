<?php
namespace App\DAL;
use Illuminate\Support\Facades\DB;
abstract class BaseDAL{
 protected function db(){return DB::connection();}
}
