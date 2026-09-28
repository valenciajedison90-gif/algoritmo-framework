<?php
namespace App\DAL;
use App\Models\User;
class UsuarioDAL extends BaseDAL{
 public function obtenerCorreo(string $correo){return User::where("email",$correo)->first();}
}
