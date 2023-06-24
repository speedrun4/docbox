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
use Docbox\utils\Input;
use stdClass;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$response = new stdClass ();
$response->ok = false;

$numbers = $_POST ['numbers'];
$years = $_POST ['years'];
$withdrawalID = Input::int("r");

if (is_array ( $numbers ) && count ( $numbers ) > 0) {
	if (is_array ( $years ) && count ( $years ) > 0) {
		if (count ( $years ) == count ( $numbers )) {
			$db = new DbConnection ();
			$controller = new WithdrawalController ( $db );

			$withDocs = array ();
			for($i = 0; $i < count ( $numbers ); $i ++) {
				$doc = new WithdrawalDoc ( intval ( $numbers [$i] ), intval ( $years [$i] ) );
				$withDocs [] = $doc;
            }

			$response->id = $controller->addDocsToWithdrawal($withdrawalID, $withDocs);
			$response->ok = $response->id > 0;
		}
	}
}

echo json_encode ( $response );