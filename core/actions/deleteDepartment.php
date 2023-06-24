<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/DepartmentController.php");

use Docbox\control\DepartmentController;
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
$response->type = "error";
$response->message = "Não foi possível concluir a operação";

$type = getReqParam("department", "int", "post");

if($type > 0) {
	$db = new DbConnection();
	$departController = new DepartmentController($db);
	if($departController->deleteDepartment($type, $user->getId())) {
		$response->ok = TRUE;
		$response->type = "success";
		$response->message = "Departamento excluído com sucesso!";
	} else {
		$response->type = "warning";
	}
} else {
	$response->message = "Parâmetros incorretos";
}

echo json_encode($response);