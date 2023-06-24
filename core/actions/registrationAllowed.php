<?php
namespace Docbox\actions;

include_once(dirname(__FILE__) . "/../utils/Utils.php");
include_once(dirname(__FILE__) . "/../utils/Input.php");
include_once(dirname(__FILE__) . "/../model/Document.php");
include_once(dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once(dirname(__FILE__) . "/../control/BoxController.php");
include_once(dirname(__FILE__) . "/../control/DocumentController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\Document;
use Docbox\utils\Input;
use function Docbox\utils\getReqParam;
use BoxController;
use Docbox\control\DocumentController;
use stdClass;

$user = getUserLogged();
if ($user == NULL || $user->getClient() <= 0) {
    exit();
}

$response = new stdClass();
$response->ok = TRUE;

$client = getReqParam("client", "int", "get");
$box_number    = getReqParam("box", "int", "get");
$type   = getReqParam("type", "int", "get");
$year   = getReqParam("year", "int", "get");
$number = getReqParam("number", "int", "get");
$letter = getReqParam("letter", "str", "get");
$volume = Input::getInt('volume');

if($volume == 0) $volume = NULL;

if (empty($letter)) {
    $letter = NULL;
} else {
    $letter = substr($letter, 0, 1);
}

if($client > 0 && $box_number > 0 && $type > 0 && $number > 0 && $year > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $boxController->getBox($client, $box_number);
    
    if($box != NULL) {
	    $doc = new Document();
	    $doc->setClient($user->getClient());
	    $doc->setBox($box);
	    $doc->setYear($year);
	    $doc->setType($type);
	    $doc->setNumber($number);
	    $doc->setLetter($letter);
	    $doc->setVolume($volume);
	
	    $docController = new DocumentController($db);
	    if($docController->docExists($doc)) {
	        $response->ok = FALSE;
	    }
    }
}

echo json_encode($response);