<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");
include_once (dirname(__FILE__) . "/../control/BookController.php");
include_once (dirname(__FILE__) . "/../control/DocumentTypeController.php");

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
$response->ok = false;
$response->response = "error";

$boo_id = getReqParam("liv_id", "int", "post");
$box_id = getReqParam("box_id", "int", "post");// Id da caixa q estava
$boo_box = getReqParam("liv_box", "int", "post");// Num da caixa
$boo_type = getReqParam("liv_type", "int", "post");
$boo_year = getReqParam("liv_year", "int", "post");
$boo_num_from = getReqParam("liv_num_from", "int", "post");
$boo_num_to = getReqParam("liv_num_to", "int", "post");
$boo_volume = getReqParam("liv_volume", "int", "post");

if ($boo_volume <= 0) $boo_volume = NULL;

if ($boo_id > 0 && $boo_box > 0 && $boo_type > 0 && $boo_year >= 1900 && $boo_year <= date('Y') && ($boo_num_from < $boo_num_to && $boo_num_from > 0)) {
	$db = new DbConnection();
	$bookController = new BookController($db);
	$boxController = new BoxController($db);

	$box = $boxController->getBoxById($box_id);
	$new_box = $boxController->getBox($user->getClient(), $boo_box);

	if ($box != NULL && $new_box != NULL) {
	    if($box->getId() != $new_box->getId() && $new_box->isSealed()) {
	        $response->type = "warning";
	        $response->error = "Caixa está selada";
	    } else {
    		$typeController = new DocumentTypeController($db);
    		$typeObj = $typeController->getTypeById($boo_type); // se !=null
    
    		$book = new Book();
    		$book->setId($boo_id);
    		$book->setClient($user->getClient());
    		$book->setBox($new_box);
    		$book->setType($typeObj);
    		$book->setYear($boo_year);
    		$book->setNumFrom($boo_num_from);
    		$book->setNumTo($boo_num_to);
    		$book->setVolume($boo_volume);
    
    		$dbBook = $bookController->getBookById($boo_id);
    		if($dbBook != NULL) {
    		    if(!$bookController->existsAnother($book)) {
    		        if(!$boxController->hasDocs($new_box)) {
            		    if ($bookController->updateBook($book, $user->getId()) > 0) {
            				$response->ok = TRUE;
            				$response->type = "success";
            			}
    		        } else {
    		            $response->type = "error";
    		            $response->error = "Caixa já cadastrada com documentos";
    		        }
    		    } else {
    		        $response->type = "warning";
    		        $response->error = "Livro já cadastrado na caixa N&ordm; " . $book->getBox()->getNumber();
    		    }
    		} else {
    		    $response->type = "error";
    		    $response->error = "Livro não existe!";
    		}
	    }
	} else {
		$response->error = "Caixa não cadastrada";
	}
} else {
	$response->error = "Parâmetros incorretos";
}
echo json_encode ( $response );