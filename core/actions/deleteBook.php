<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BookController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use BookController;
use stdClass;

$user = getUserLogged();
if($user == NULL) {
	exit();
}

$response = new  stdClass();
$response->ok = false;
$response->response = "error";

$book = NULL;
$boo_id = getReqParam("book", "int", "post");

if($boo_id > 0) {
	$db = new DbConnection();
	$bookController = new BookController($db);
	$book = $bookController->getBookById($boo_id);

	if($book != NULL) {
	    if($bookController->deleteBook($book->getId(), $user->getId())) {
    		$response->ok = TRUE;
    		$response->type = "success";
    		$response->req_id = $book->getId();
    	} else {
    		$response->type = "warning";
    		$response->error = "Não foi possível concluir a requisição";
    	}
    } else {
    	$response->type = "error";
    	$response->error = "Parâmetros incorretos";
    }
}

echo json_encode($response);