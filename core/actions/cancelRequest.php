<?php
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");
include_once (dirname(__FILE__) . "/../model/Request.php");
include_once (dirname(__FILE__) . "/../model/RequestStatus.php");

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;

$user = getUserLogged();

if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

$req_id = getReqParam("r", "int", "get");

if($req_id > 0) {
	$db = new DbConnection();
	$reqController = new RequestController($db);

	$request = $reqController->getRequest($req_id);
	if($request != NULL) {
		if($reqController->setRequestStatus($request, RequestStatus::CANCELED, $user->getId())) {
			$response->ok = TRUE;
			$response->type = "success";
		} else {
			$response->type = "warning";
			$response->error = "Erro ao cancelar pedido!";
		}
	} else {
		$response->error = "Parâmetros incorretos";
	}
} else {
	$response->error = "Parâmetros incorretos";
}

echo json_encode($response);