<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/ClientController.php");
include_once (dirname(__FILE__) . "/../model/User.php");

use Docbox\control\ClientController;
use function Docbox\control\getUserLogged;
use function Docbox\control\setUserClient;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\getReqParam;
use stdClass;

$user = getUserLogged();
if($user == NULL || $user->getProfile() != User::USER_ADMIN) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

$client = getReqParam("client", "int", "post");

if($client > 0) {
	$db = new DbConnection();
	$cliController = new ClientController($db);

	$oClient = $cliController->getClient($client);
	if($oClient != NULL) {
		setUserClient($client);
		$response->ok = TRUE;
	} else {
		$response->type = "warning";
		$response->error = "Não foi possível concluir a operação";
	}
} else {
	$response->type = "error";
	$response->error = "Parâmetros incorretos";
}

echo json_encode($response);