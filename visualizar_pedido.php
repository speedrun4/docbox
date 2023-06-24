<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/model/RequestType.php");
include_once (dirname(__FILE__) . "/core/model/RequestStatus.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/RequestController.php");
include_once (dirname(__FILE__) . "/core/model/AbstractDocument.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\AbstractDocumentFormat;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use Docbox\model\RequestType;
use Docbox\model\User;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$reqController = new RequestController($db);
$request = $reqController->getRequest(intval($_GET['r']));

if($request == NULL || $request->getClient() != $user->getClient())  {
	header("Location: login.php");
}

if($request->getType() == RequestType::DOCUMENT) {
	header("Location: visualizar_pedido_documentos.php?r=" . $request->getId());
} else if($request->getType() == RequestType::BOX) {
	header("Location: visualizar_pedido_caixas.php?r=" . $request->getId());
}
?>