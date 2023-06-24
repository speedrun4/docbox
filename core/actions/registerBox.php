<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");
include_once (dirname(__FILE__) . "/../model/User.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\getReqParam;
use BoxController;
use Exception;
use stdClass;

$user = getUserLogged();
if ($user == NULL || $user->getProfile() != User::USER_ADMIN) {
	exit();
}

$response = new stdClass();
$response->ok = FALSE;
$response->type = "error";
$response->message = "Não foi possível executar a operação";

$client = $user->getClient();
$number = getReqParam("box_number", "int", "post");
$department = getReqParam("box_department", "int", "post");

if ($client > 0 && $number > 0 && $department > 0) {
	$db = new DbConnection();
	$boxController = new BoxController($db);

	if (! $boxController->boxExists($client, $number)) {
		try {
			$response->ok = $boxController->registerBox($client, $number, $department);
		} catch (Exception $e) {
			// echo 'Exceção capturada: ', $e->getMessage(), "\n";
		}
	} else {
		$response->type = "warning";
		$response->message = "A caixa já foi cadastrada!";
	}
}

echo json_encode($response);