<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Input.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/DevolutionController.php");

use Docbox\control\DevolutionController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$response = new \stdClass ();
$response->ok = FALSE;

$user = getUserLogged ();
if ($user != NULL) {
	$ids = isset ( $_POST ['ids'] ) ? $_POST ['ids'] : array ();

	if (count ( $ids ) > 0) {
		$db = new DbConnection ();
		$devController = new DevolutionController ( $db );

		$id = $devController->registerPartialBoxDevolution ( $ids, $user->getId () );
		if ($id > 0) {
			$response->ok = TRUE;
			$response->type = "success";
			$response->dev_id = $id;
		} else {
			$response->type = "warning";
			$response->error = "Não foi possível concluir a requisição";
		}
	} else {
		$response->type = "error";
		$response->error = "Parâmetros incorretos";
	}
}

echo json_encode ( $response );
