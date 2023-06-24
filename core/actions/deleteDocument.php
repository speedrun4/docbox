<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/DocumentController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
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
$doc_id = getReqParam("doc", "int", "post");

if($doc_id > 0) {
	$db = new DbConnection();
	$docController = new DocumentController($db);
	$document = $docController->getDocumentById($doc_id);
}

if($document != NULL) {
    if(!$document->getBox()->isSealed()) {
    	if($docController->deleteDocument($document->getId(), $user->getId())) {
    		$response->ok = TRUE;
    		$response->type = "success";
    		$response->req_id = $document->getId();
    	} else {
    		$response->type = "warning";
    		$response->error = "Não foi possível concluir a requisição";
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