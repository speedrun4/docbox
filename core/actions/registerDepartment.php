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
$response->message = "Não foi possível realizar a operação";
$response->type = "error";

$type = trim(getReqParam("type_name", "str", "post"));

if(!empty($type) && mb_strlen($type) < 64) {
	$db = new DbConnection();
	$departmentController = new DepartmentController($db);

	if(!$departmentController->departmentExists($type, $user->getClient())) {
	    
	    if($departmentController->registerDepartment($type, $user)) {
			$response->ok = TRUE;
			$response->type = "success";
		}
	} else {
		$response->type = "warning";
		$response->message = "Departamento já existe";
	}
} else {
    $response->message = "Parâmetros incorretos";
}

echo json_encode($response);