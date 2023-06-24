<?php
include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/WithdrawalController.php");
include_once (dirname(__FILE__) . "/../model/Withdrawal.php");
include_once (dirname(__FILE__) . "/../model/WithdrawalStatus.php");

use Docbox\control\WithdrawalController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\WithdrawalStatus;
use Docbox\utils\Input;

$user = getUserLogged();

if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = FALSE;
$response->response = "error";

$withdrawalID = Input::getInt("r");

if($withdrawalID > 0) {
	$db = new DbConnection();
	$withController = new WithdrawalController($db);

	$withdrawal = $withController->getWithdrawalById($withdrawalID, $user->getClient());
	if($withdrawal != NULL && $withdrawal->getClient() == $user->getClient()) {
		if($withController->cancelWithdrawal($withdrawalID, $user->getId())) {
			$response->ok = TRUE;
			$response->type = "success";
		} else {
			$response->type = "warning";
			$response->error = "Erro ao realizar o cancelamento!";
		}
	} else {
		$response->type = "error";
		$response->error = "Parâmetros incorretos";
	}
} else {
	$response->type = "error";
	$response->error = "Parâmetros incorretos";
}

echo json_encode($response);