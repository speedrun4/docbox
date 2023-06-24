<?php
namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../model/Document.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/BoxController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentTypeController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use DateTime;
use Docbox\control\DocumentController;
use stdClass;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$response = new stdClass ();
$response->ok = false;
$response->response = "error";

$box = getReqParam ( "box", "int", "post" );
$uploadedFilename = getReqParam ( "filename", "str", "post" );
$token = getReqParam ( "doc_token", "str", "post" );

// Se o arquivo temporário existe...
if ($box > 0 && !empty($uploadedFilename) && ! empty ( $token )) {
	if (file_exists ( dirname ( __FILE__ ) . "/../../temp_files/$token" )) {
		$token = dirname ( __FILE__ ) . "/../../temp_files/$token";
		$hash_file = hash_file ( 'sha1', $token );

		$db = new DbConnection ();
		$documentController = new DocumentController ( $db );
		$existentPDF = FALSE;

		if (! empty ( $hash_file )) {
			$existentPDF = $documentController->docFileExists ( $hash_file );
		}

		if (empty ( $existentPDF )) {
			$dbDocumentResult = $documentController->getDocumentByBoxFilename($box, $uploadedFilename, $user->getClient());

			if(!empty($dbDocumentResult) && get_class($dbDocumentResult) == "Docbox\utils\Result") {
				if($dbDocumentResult->isOk() && get_class($dbDocumentResult->getResult()) == "Docbox\model\Document" && !$dbDocumentResult->getResult()->isDead ()) {
					$dbDocument = $dbDocumentResult->getResult();
					// Se o arquivo temporário existe...
						$folder = dirname ( __FILE__ ) . "/../../doc_files/" . $dbDocument->getYear () . "/";
						if (! is_dir ( $folder ))
							mkdir ( $folder, 0777, true );
	
						$filename = sha1 ( $user->getClient () . (new DateTime ( "now" ))->getTimestamp () . rand ( 1, 666 ) . sprintf ( "%04d%04d%s%s_%4d.pdf", $dbDocument->getBox ()->number, $dbDocument->getNumber (), strtoupper ( $dbDocument->getLetter () ), ($dbDocument->getVolume () > 0 ? ("_VOL" . $dbDocument->getVolume ()) : ""), $dbDocument->getYear () ) ) . ".pdf";
	
						if (file_exists ( $token )) {
							if (rename ( $token, $folder . $filename )) { // Move o arquivo enviado
								if ($documentController->updateDocFile ( "doc_files/" . $dbDocument->getYear () . "/$filename", $hash_file, $dbDocument->getId(), $user->getId () )) {
									$response->ok = TRUE;
									$response->type = "success";
								} else {
									unlink($folder . $filename);
									$response->type = "error";
									$response->error = "Erro no upload do arquivo!";
								}
							} else {
								unlink($token);
								$response->type = "error";
								$response->error = "Erro no upload do arquivo!";
							}
						} else {
							$response->type = "error";
							$response->error = "Erro no upload do arquivo!";
						}
				} else {
					$response->type = "error";
					$response->error = $dbDocument->getFormattedMessage();
					unlink ( $token ); // Exclui o arquivo enviado
				}
			} else {
				$response->type = "error";
				$response->error = "Documento não encontrado na caixa!";
				unlink ( $token ); // Exclui o arquivo enviado
			}
		} else {
			$response->type = "warning";
			$response->error = "Este arquivo PDF já existe no servidor! <br>" . $existentPDF->getType ()->getDescription () . " Nº " . $existentPDF->getNumber () . "" . $existentPDF->getLetter () . "/" . $existentPDF->getYear () . "<br>Caixa Nº " . $existentPDF->getBox ()->getNumber ();
			unlink ( $token ); // Exclui o arquivo enviado
		}
	} else {
		$response->type = "error";
		$response->error = "Erro no upload do arquivo!";
	}
} else {
	$response->type = "error";
	$response->error = "Campos obrigatórios não informados!";
}

echo json_encode ( $response );