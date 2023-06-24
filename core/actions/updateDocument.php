<?php

namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/BoxController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentTypeController.php");
include_once (dirname ( __FILE__ ) . "/../model/Document.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\Document;
use function Docbox\utils\getReqParam;
use BoxController;
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

if (isset ( $_GET ['files'] )) {
	$allowedExtensions = array (
			"pdf"
	);
	$token = "";
	$files = array ();

	// Cria o token do upload
	$token = sha1 ( "ommmmmmmm" . rand ( 1008, 20000 ) . "" . (new DateTime ( "now" ))->getTimestamp () );
	$uploaddir = dirname ( __FILE__ ) . "/../../temp_files/";
	if (! is_dir ( $uploaddir )) {
		mkdir ( $uploaddir, 0777, TRUE );
	}

	if (count ( $_FILES ) == 1) {
		foreach ( $_FILES as $file ) { // Deve ser somente um arquivo
		                               // upload
			if ($file ["size"] < MAX_UPLOAD_MB * MB) {
				$extension = strtolower ( substr ( $file ['name'], strripos ( $file ['name'], "." ) + 1 ) );

				if (in_array ( $extension, $allowedExtensions )) {
					if (! in_array ( $file ['name'], $files )) {
						if (move_uploaded_file ( $file ['tmp_name'], $uploaddir . $token )) {
							$response->ok = true;
							$response->token = $token;
						} else {
							$response->ok = false;
							$response->error = 'Ocorreu um erro ao realizar o upload dos arquivos.';
						}
					}
				} else {
					$response->ok = false;
					$response->error = 'Esta nao é uma extensão permitida!';
				}
			} else {
				// do not upload
				$response->ok = false;
				$response->error = 'O arquivo excede o tamanho máximo permitido (' . MAX_UPLOAD_MB . 'Mb)!';
			}
		}
	} else {
		$response->ok = false;
		$response->error = 'Erro no processamento da operação!';
	}

	echo json_encode ( $response );
} else {
	$id = getReqParam ( "doc_id", "int", "post" );
	$box_id = getReqParam ( "box_id", "int", "post" );
	$doc_box = getReqParam ( "doc_box", "int", "post" );
	$type = getReqParam ( "doc_type", "int", "post" );
	$year = getReqParam ( "doc_year", "int", "post" );
	$number = getReqParam ( "doc_number", "int", "post" );
	$letter = getReqParam ( "doc_letter", "str", "post" );
	$volume = getReqParam ( "doc_volume", "int", "post" );
	$company = getReqParam ( "doc_company", "str", "post" );
	$date = getReqParam ( "doc_date", "str", "post" );
	$token = getReqParam ( "doc_token", "str", "post" );

	if (! empty ( $date )) {
		$date = DateTime::createFromFormat ( "Y-m-d", $date );
	} else {
		$date = NULL;
	}

	if (empty ( $letter )) {
		$letter = NULL;
	} else {
		$letter = substr ( $letter, 0, 1 );
	}

	if ($volume <= 0) {
		$volume = NULL;
	}

	$hash_file = NULL;
	// Se o arquivo temporário existe...
	if (! empty ( $token )) {
		if (file_exists ( dirname ( __FILE__ ) . "/../../temp_files/$token" )) {
			$token = dirname ( __FILE__ ) . "/../../temp_files/$token";
			$hash_file = hash_file ( 'sha1', $token );
		} else {
			$response->type = "error";
			$response->error = "Erro no upload do arquivo!";
		}
	}
	
	$response->hash = $hash_file;

	$db = new DbConnection ();
	$documentController = new DocumentController ( $db );
	$existentPDF = FALSE;

	if (! empty ( $hash_file )) {
		$existentPDF = $documentController->docFileExists ( $hash_file );
	}

	if (empty($existentPDF)) {
		if ($id > 0 && $box_id > 0 && $type > 0 && $year >= 0 && $year <= date ( 'Y' )) {
			$boxController = new BoxController ( $db );

			$box = $boxController->getBoxById ( $box_id );
			$newBox = $boxController->getBox ( $user->getClient (), $doc_box );

			if ($newBox != NULL && $box != NULL) {
				if ($newBox->getId () != $box->getId () && $newBox->isSealed ()) { // Se for em outra caixa, ela deve estar não selada
					$response->type = "warning";
					$response->error = "Caixa está selada";
				} else { // Permite alteração, mesmo em caixa selada
					$document = new Document ();
					$document->setId ( $id );
					$document->setClient ( $user->getClient () );
					$document->setBox ( $newBox );
					$document->setType ( $type );
					$document->setYear ( $year );
					$document->setNumber ( $number );
					$document->setLetter ( $letter );
					$document->setVolume ( $volume );
					$document->setCompany ( $company );
					$document->setDate ( $date );
					$document->setHash($hash_file);

					$dbDocument = $documentController->getDocumentById ( $id );

					if ($dbDocument != NULL) {
						if (! $documentController->existsAnother ( $document )) {
							if (! $boxController->hasBooks ( $newBox )) {
								// Se o arquivo temporário existe...
								if (! empty ( $token )) {
									$folder = dirname ( __FILE__ ) . "/../../doc_files/$year/";
									if (! is_dir ( $folder ))
										mkdir ( $folder, 0777, true );

									$filename = $filename = sha1 ( $user->getClient () . (new DateTime ( "now" ))->getTimestamp () . rand ( 1, 666 ) . sprintf ( "%04d%04d%s%s_%4d.pdf", $dbDocument->getBox ()->number, $number, strtoupper ( $letter ), ($volume > 0 ? "_VOL$volume" : ""), $year ) ) . ".pdf";

									if (file_exists ( $token )) {
										if (rename ( $token, $folder . $filename )) { // Move o arquivo enviado
											if ($documentController->updateDocFile ( "doc_files/$year/" . $filename, $hash_file, $id, $user->getId () )) {
												// Atualiza o documento
												if ($documentController->updateDocument ( $document, $user->getId () )) {
													$response->ok = TRUE;
													$response->type = "success";
												} else {
													$response->type = "error";
													$response->error = "Erro ao atualizar o documento!";
												}
											} else {
												$response->type = "error";
												$response->error = "Erro no upload do arquivo!";
											}
										} else {
											$response->type = "error";
											$response->error = "Erro no upload do arquivo!";
										}
									} else {
										$response->type = "error";
										$response->error = "Erro no upload do arquivo!";
									}
								} else {
									// Atualiza o documento
									if ($documentController->updateDocument ( $document, $user->getId () )) {
										$response->ok = TRUE;
										$response->type = "success";
									}
								}
							} else {
								$response->type = "error";
								$response->error = "Caixa já cadastrada com livros!";
							}
						} else {
							$response->type = "warning";
							$response->error = "Documento já cadastrado na caixa N&ordm; " . $document->getBox ()->getNumber ();
						}
					} else {
						$response->type = "error";
						$response->error = "Documento não existe!";
					}
				}
			} else {
				$response->type = "error";
				$response->error = "Não foi possível encontrar a caixa";
			}
		} else {
			$response->type = "error";
			$response->error = "Parâmetros incorretos";
		}
	} else {
		$response->type = "warning";
		$response->error = "Este arquivo PDF já existe no servidor! <br>". $existentPDF->getType()->getDescription() . " Nº " . $existentPDF->getNumber() . "" . $existentPDF->getLetter() . "/" . $existentPDF->getYear() .
		"<br>Caixa Nº " . $existentPDF->getBox()->getNumber();
		// Exclui o arquivo enviado
		unlink ( $token );
	}

	echo json_encode ( $response );
}