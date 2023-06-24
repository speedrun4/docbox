<?php
include_once (dirname ( __FILE__ ) . "/core/utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/core/utils/Input.php");
include_once (dirname ( __FILE__ ) . "/core/model/Request.php");
include_once (dirname ( __FILE__ ) . "/core/model/RequestType.php");
include_once (dirname ( __FILE__ ) . "/core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserController.php");
include_once (dirname ( __FILE__ ) . "/core/control/RequestController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DevolutionController.php");

use Docbox\control\DevolutionController;
use function Docbox\control\getUserLogged;
use Docbox\model\AbstractDocumentFormat;
use Docbox\model\DbConnection;
use Docbox\model\RequestType;
use Docbox\utils\Input;
use Docbox\model\Devolution;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	header ( "Location: login.php" );
}

$db = new DbConnection ();
$dev_id = Input::getInt ( 'dev' );
$devolutionController = new DevolutionController ( $db );

$devolution = $devolutionController->getDevolutionById ( $dev_id );

if ($devolution == NULL) {
	header ( "Location: login.php" );
}
if($devolution->getReqType() == RequestType::DOCUMENT) {
	header("Location: visualizar_devolucao_documentos.php?dev=$dev_id");
} else if($devolution->getReqType() == RequestType::BOX) {
	header("Location: visualizar_devolucao_caixas.php?dev=$dev_id");
}
?>