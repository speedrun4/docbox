<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");
include_once (dirname(__FILE__) . "/../control/BookController.php");
include_once (dirname(__FILE__) . "/../control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/../model/Book.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\Book;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use BookController;
use BoxController;
use stdClass;

$user = getUserLogged();
if ($user == NULL || $user->getClient() <= 0) {
	exit();
}

$response = new stdClass();
$response->ok = FALSE;
$response->response = "error";

$boo_box = getReqParam("liv_box", "int", "post");
$boo_type = getReqParam("liv_type", "int", "post");
$boo_year = getReqParam("liv_year", "int", "post");
$boo_num_from = getReqParam("liv_num_from", "int", "post");
$boo_num_to = getReqParam("liv_num_to", "int", "post");
$boo_volume = getReqParam("liv_volume", "int", "post");
$sealed = getReqParam("sealed", "boolean", "post");

if ($boo_volume <= 0) $boo_volume = NULL;

if ($boo_box > 0 && $boo_type > 0 && $boo_year >= 1900 && $boo_year <= date('Y') && ($boo_num_from < $boo_num_to && $boo_num_from > 0)) {
	$db = new DbConnection();
	$bookController = new BookController($db);
	$boxController = new BoxController($db);

	$box = $boxController->getBox($user->getClient(), $boo_box);

	if ($box != NULL) {
	    if(!$box->isSealed()) {
    	    if(!$boxController->hasDocs($box)) {
    	        $typeController = new DocumentTypeController($db);
    	        $typeObj = $typeController->getTypeById($boo_type); // se !=null
    	        
    	        $book = new Book();
    	        $book->setClient($user->getClient());
    	        $book->setBox($box);
    	        $book->setType($typeObj);
    	        $book->setYear($boo_year);
    	        $book->setNumFrom($boo_num_from);
    	        $book->setNumTo($boo_num_to);
    	        $book->setVolume($boo_volume);
    	        
    	        if (!$bookController->bookExists($book)) {
    	            if ($bookController->insertBook($book, $user->getId()) > 0) {
    	                $response->ok = TRUE;
    	                $response->type = "success";
    	                if($sealed) $boxController->sealBox($box->getId(), $sealed, $user->getId());
    	            }
    	        } else {
    	            $response->type = "warning";
    	            $response->error = "Livro já cadastrado na caixa N&ordm; " . $book->getBox()->getNumber();
    	        }
    	    } else {
    	        $response->type = "warning";
    	        $response->error = "Caixa já cadastrada com documentos";
    	    }
	    } else {
	        $response->type = "warning";
	        $response->error = "Caixa está selada";
	    }
	} else {
		$response->error = "Caixa não cadastrada";
	}
} else {
	$response->error = "Parâmetros incorretos";
}
echo json_encode ( $response );