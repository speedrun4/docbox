<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use stdClass;

$user = getUserLogged();
if ($user == NULL) {
	exit();
}

$response = new stdClass();
$response->ok = false;
$response->ids = array();

$req_id = Input::int('req');
if($req_id > 0) {
	$reqController = new RequestController(new DbConnection());
	$response->ids = $reqController->getDevolutionAvailableDocumentIds($req_id);
	$response->ok = true;
}

echo json_encode($response);