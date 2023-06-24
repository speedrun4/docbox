<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/DocumentTypeController.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use stdClass;

$user = getUserLogged();
if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

$type = getReqParam("t", "int", "post");

if($type > 0) {
	$db = new DbConnection();
	$typeController = new DocumentTypeController($db);
	if($typeController->deleteType($type, $user->getId())) {
		$response->ok = TRUE;
		$response->type = "success";
	} else {
		$response->type = "warning";
		$response->error = "Não foi possível concluir a operação";
	}
} else {
	$response->type = "error";
	$response->error = "Parâmetros incorretos";
}

echo json_encode($response);