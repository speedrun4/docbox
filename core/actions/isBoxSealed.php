<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/BoxController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use BoxController;
use stdClass;

$user = getUserLogged ();
if ($user != NULL && $user->getClient () > 0) {
	$response = new stdClass ();
	$response->ok = false;

	$box = getReqParam ( "box_number", "int", "get" );

	if ($box > 0) {
		$db = new DbConnection ();
		$boxController = new BoxController ( $db );
		$box = $boxController->getBox ( $user->getClient (), $box );
		if ($box != NULL) {
			$response->ok = $box->isSealed ();
		}
	}

	echo json_encode ( $response );
} else {
	exit ();
}