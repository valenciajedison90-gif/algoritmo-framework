<?php
namespace App\BLL;
use App\ADO\ADOUsuario;
use App\DAL\UsuarioDAL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class LoginBLL extends BaseBLL{
 public function __construct(private UsuarioDAL $dal){}
 public function autenticar(ADOUsuario $ado){
   $u=$this->dal->obtenerCorreo($ado->correo);
   if(!$u) return ["estado"=>false,"mensaje"=>"Usuario no existe"];
   if(!Hash::check($ado->password,$u->password)) return ["estado"=>false,"mensaje"=>"Contraseña incorrecta"];
   Auth::login($u);
   return $this->ok("Bienvenido",$u);
 }
}
