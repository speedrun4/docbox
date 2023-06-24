<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/DepartmentController.php");

use Docbox\control\DepartmentController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use stdClass;

$user = getUserLogged();
if ($user == NULL) {
	exit();
}

$response = new stdClass();
$response->ok = false;
$response->type = "error";
$response->message = "Não foi possível realizar a operação";

$type_id = trim(getReqParam("type_id", "str", "post"));
$type_name = trim(getReqParam("type_name", "str", "post"));

if ($type_id > 0 && ! empty ($type_name) && mb_strlen($type_name) < 64) {
	$db = new DbConnection();
	$departController = new DepartmentController($db);

	$type = $departController->getDepartmentById($type_id);

	if ($type != NULL) {
	    if ($departController->updateDepartment($type_id, $type_name, $user->getId())) {
			$response->ok = TRUE;
			$response->type = "success";
			$response->message = "Operação realizada com sucesso!";
		}
	} else {
		$response->type = "warning";
		$response->message = "Parâmetros incorretos";
	}
} else {
	$response->message = "Parâmetros incorretos";
}

echo json_encode($response);