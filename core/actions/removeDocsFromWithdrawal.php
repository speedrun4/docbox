<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Input.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../model/Withdrawal.php");
include_once (dirname ( __FILE__ ) . "/../model/WithdrawalDoc.php");
include_once (dirname ( __FILE__ ) . "/../control/WithdrawalController.php");

use Docbox\control\WithdrawalController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\WithdrawalDoc;
use Docbox\model\WithdrawalStatus;
use Docbox\utils\Input;
use stdClass;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$response = new stdClass ();
$response->ok = FALSE;

$docs = $_POST ['documents'];
$withdrawalID = Input::int("r");

if ($withdrawalID > 0 && is_array ( $docs ) && count ( $docs ) > 0) {
	$db = new DbConnection ();
	$controller = new WithdrawalController ( $db );

	$withdrawal = $controller->getWithdrawalById($withdrawalID, $user->getClient ());

	if($withdrawal != NULL && $withdrawal->getStatus() == WithdrawalStatus::OPEN) {
		$response->ok = $controller->removeDocsFromWithdrawal($withdrawalID, $docs, $user->getId());
	}
}

echo json_encode ( $response );