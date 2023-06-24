<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/DocumentController.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use Docbox\control\DocumentController;
use stdClass;

$user = getUserLogged();
if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

$document = NULL;
$doc_id = Input::getInt("doc");

if($doc_id > 0) {
	$db = new DbConnection();
	$docController = new DocumentController($db);
	$document = $docController->getDocumentById($doc_id);
}

if($document != NULL && !empty($document->getFile())) {
    if(!$document->getBox()->isSealed()) {
    	if($docController->deleteFile($document, $user->getId())) {
    		$response->ok = TRUE;
    		$response->type = "success";
    	} else {
    		$response->type = "warning";
    		$response->error = "Não foi possível excluir o arquivo";
    	}
    } else {
        $response->type = "error";
        $response->error = "A caixa está selada";
    }
} else {
	$response->type = "error";
	$response->error = "Parâmetros incorretos";
}

echo json_encode($response);