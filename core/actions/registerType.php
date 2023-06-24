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
	exit ();
}

$response = new stdClass ();
$response->ok = false;
$response->error = "Não foi possível realizar a operação";
$response->type = "error";

$type = trim ( getReqParam ( "type_name", "str", "post" ) );
$preffix = strtoupper ( trim ( getReqParam ( "type_preffix", "str", "post" ) ) );

if (! empty ( $type ) && mb_strlen ( $type ) < 45 && ! empty ( $preffix ) && mb_strlen ( $preffix ) == DocumentType::PREFFIX_LENGTH) {
	$db = new DbConnection ();
	$typeController = new DocumentTypeController ( $db );

	$typeExists = FALSE;
	$types = $typeController->getTypes ( $user->client );
	foreach ( $types as $dbType ) {
		if (strcasecmp ( $dbType->description, $type ) == 0 || strcasecmp ( $dbType->preffix, $preffix ) == 0) {
			$typeExists = TRUE;
		}
	}

	if (! $typeExists) {
		if ($typeController->insertType ( $type, $preffix, $user )) {
			$response->ok = TRUE;
			$response->type = "success";
		}
	} else {
		$response->type = "warning";
		$response->message = "O tipo/prefixo informado já existe!";
	}
} else {
	$response->error = "Parâmetros incorretos";
}

echo json_encode ( $response );