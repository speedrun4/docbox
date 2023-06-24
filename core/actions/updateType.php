<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentTypeController.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use stdClass;
use Docbox\model\DocumentType;

$user = getUserLogged ();
if ($user == NULL) {
	exit();
}

$response = new stdClass ();
$response->ok = false;
$response->error = "Não foi possível realizar a operação";
$response->type = "error";

$type_id = trim ( getReqParam ( "up_type_id", "str", "post" ) );
$type_name = trim ( getReqParam ( "up_type_name", "str", "post" ) );
$type_preffix = strtoupper(trim ( getReqParam ( "up_type_preffix", "str", "post" ) ));

if ($type_id > 0 && ! empty ( $type_name ) && mb_strlen ( $type_name ) < 45 && !empty($type_preffix) && mb_strlen($type_preffix) == DocumentType::PREFFIX_LENGTH) {
	$db = new DbConnection ();
	$typeController = new DocumentTypeController ( $db );

	$typeByPreffix = $typeController->getTypeByPreffix($type_preffix, $user->getClient());

	if($typeByPreffix == NULL) {
		$type = $typeController->getTypeById ( $type_id );
		if ($type != NULL) {
			if ($typeController->updateType ( $type_id, $type_name, $type_preffix, $user->getId())) {
				$response->ok = TRUE;
				$response->type = "success";
			}
		} else {
			$response->type = "warning";
			$response->error = "Parâmetros incorretos";
		}
	} else {
		$response->type = "warning";
		$response->error = "Prefixo já existente";
	}
} else {
	$response->error = "Parâmetros incorretos";
}

echo json_encode ( $response );