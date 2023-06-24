<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;
use BoxController;
use stdClass;

function isBlockedByRequest($box) {
    if ($box->getRequest() == NULL || $box->getRequest()->getStatus() == RequestStatus::CANCELED || $box->getRequest()->getStatus() == RequestStatus::RETURNED || $box->getRequest()->getStatus() == RequestStatus::COMPLETED) {
        return FALSE;
    }
    return TRUE;
}

$user = getUserLogged();
if ($user == NULL) {
    exit();
}

$response = new stdClass();
$response->ok = FALSE;
$response->message = "Não foi possível concluir a operação";

$box_from = getReqParam("box_from", "int", "post");
$box_to = getReqParam("box_to", "int", "post");
$selectedDocuments = isset($_POST['documents']) ? $_POST['documents'] : array();
$selectedDocuments = is_array($selectedDocuments) ? $selectedDocuments : array();

if ($box_from > 0 && $box_to > 0 && $box_from != $box_to && count($selectedDocuments) > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box_from = $boxController->getBoxById($box_from);
    $box_to = $boxController->getBoxById($box_to);
    
    if($boxController->isBoxEmpty($box_to) || 
        $boxController->hasBooks($box_from) && $boxController->hasBooks($box_to) || 
            $boxController->hasDocs($box_from) && $boxController->hasDocs($box_to)) {
        if ($box_from != NULL && $box_from->getClient() == $user->getClient() && 
            $box_to != NULL && $box_to->getClient() == $user->getClient()) {
            // Se a caixa está em pedido não concluído
            if (! isBlockedByRequest($box_from) && ! isBlockedByRequest($box_to)) {
                // Envia os documentos para outra caixa
                if ($boxController->transferDocumentList($box_from->getId(), $box_to->getId(), $selectedDocuments, $user->getId())) {
                    $response->ok = TRUE;
                } else {
                    $response->type = "error";
                    $response->message = "Não foi possível transferir os documentos";
                }
            } else {
                $response->type = "warning";
                $response->message = "A caixa se encontra em pedido. Solucione a situação do pedido e tente novamente.";
            }
        } else {
            $response->type = "warning";
            $response->message = "Caixa não encontrada";
        }
    } else {
        $response->type = "error";
        $response->message = "Caixas são incompatíveis. A caixa deve conter somente livros ou somente documentos.";
    }
} else {
    $response->type = "error";
    $response->message = "Parâmetros incorretos";
}

echo json_encode($response);