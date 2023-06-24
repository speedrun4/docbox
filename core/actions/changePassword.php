<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserController.php");
include_once (dirname(__FILE__) . "/../control/RecoverPasswordController.php");

use Docbox\control\UserController;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use RecoverPasswordController;
use stdClass;

$token = getReqParam("token", "str", "post");
$pass1 = getReqParam("password", "str", "post");
$pass2 = getReqParam("conf-password", "str", "post");

$json_result = new stdClass();
$json_result->ok = false;

if(!empty($pass1) && !empty($pass2)) {
	if(!($pass1 != $pass2)) {
		if(strlen($pass1) >= 6) {
			$db = new DbConnection();
			$recController = new RecoverPasswordController($db);
			$usrController = new UserController($db);

			if($token = $recController->getTokenIfValid($token)) {
			    if($recController->invalidateToken($token->tok_id) && $usrController->updateUserPass($token->tok_user, sha1($pass1))) {
			        $json_result->ok = true;
					$json_result->msg = "Senha atualizada com sucesso!";
			    }
			} else {
			    $json_result->msg = "Token inválido solicite novamente a troca de senha";
			}

		} else {
			$json_result->msg = "Senha com tamanho inferior ao permitido!";
		}
	} else {
		$json_result->msg = "Senhas não conferem";
	}
} else {
	$json_result->msg = "Preencha os dois campos de senha";
}

echo json_encode($json_result);