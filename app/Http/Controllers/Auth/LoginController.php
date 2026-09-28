<?php
namespace App\Http\Controllers\Auth;
use App\ADO\ADOUsuario;
use App\BLL\LoginBLL;
class LoginController{
 public function login($request,LoginBLL $bll){
  $ado=new ADOUsuario();
  $ado->correo=$request->email;
  $ado->password=$request->password;
  return $bll->autenticar($ado);
 }
}
