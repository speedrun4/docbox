<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/DevolutionController.php");

use Docbox\control\DevolutionController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use stdClass;

$user = getUserLogged();
if($user == NULL || !$user->isAdmin()) {
	exit();
}

$response = new  stdClass();
$response->ok = false;

$devolution = Input::getInt("devolution");

if($devolution > 0) {
	$db = new DbConnection();
	$devController = new DevolutionController($db);
	$devolution = $devController->getDevolutionById($devolution);

	if($devolution != NULL) {
		if($devController->deleteDevolutionFile($devolution, $user->getId())) {
			$response->ok = TRUE;
		}
	}
}

echo json_encode($response);