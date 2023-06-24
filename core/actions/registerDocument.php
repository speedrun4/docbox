<?php

namespace Docbox\actions;

include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../model/Document.php");
include_once (dirname ( __FILE__ ) . "/../control/BoxController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentController.php");
include_once (dirname ( __FILE__ ) . "/../control/DocumentTypeController.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\Document;
use function Docbox\utils\getReqParam;
use BoxController;
use DateTime;
use Docbox\control\DocumentController;
use stdClass;
use Docbox\utils\Utils;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	exit ();
}

$response = new stdClass ();
$response->ok = false;
$response->error = "error";

if (isset ( $_GET ['files'] )) {
	$box_ok = FALSE;
	$box_number = isset ( $_REQUEST ['doc_box'] ) ? intval ( $_REQUEST ['doc_box'] ) : 0;
	if ($box_number > 0) {
		$db = new DbConnection ();
		$boxController = new BoxController ( $db );

		$box = $boxController->getBox ( $user->getClient (), $box_number );
		if ($box != NULL && ! $box->isSealed ()) {
			$box_ok = TRUE;
		}
	}

	if (count ( $_FILES ) == 1) {
		if ($box_ok) {
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

			foreach ( $_FILES as $file ) { // Deve ser somente um arquivo
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
						$response->error = 'Esta não é uma extensão permitida!';
					}
				} else {
					// do not upload
					$response->ok = false;
					$response->error = 'O arquivo excede o tamanho máximo permitido (' . MAX_UPLOAD_MB . 'Mb)!';
				}
			}
		} else {
			$response->ok = false;
			$response->type = "warning";
			$response->error = 'Caixa está selada';
		}
	} else {
		$response->ok = false;
		$response->error = 'Parâmetros incorretos';
	}

	echo json_encode ( $response );
} else {
	$box_number = getReqParam ( "doc_box", "int", "post" );
	$type = getReqParam ( "doc_type", "int", "post" );
	$year = getReqParam ( "doc_year", "int", "post" );
	$number = getReqParam ( "doc_number", "int", "post" );
	$letter = getReqParam ( "doc_letter", "str", "post" );
	$volume = getReqParam ( "doc_volume", "int", "post" );
	$date = getReqParam ( "doc_date", "str", "post" );
	$company = getReqParam ( "doc_company", "str", "post" );
	$token = getReqParam ( "doc_token", "str", "post" );
	$sealed = getReqParam ( "sealed", "boolean", "post" );

	if (! empty ( $date )) {
		$date = DateTime::createFromFormat ( "Y-m-d", $date );
	} else {
		$date = NULL;
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

	// Verifica se o arquivo enviado já existe no banco de dados

	if (empty ( $letter )) {
		$letter = NULL;
	} else {
		$letter = substr ( $letter, 0, 1 );
	}

	if ($volume <= 0) {
		$volume = NULL;
	}

	$db = new DbConnection ();
	$documentController = new DocumentController ( $db );

	$existentPDF = FALSE;

	if (! empty ( $hash_file )) {
		$existentPDF = $documentController->docFileExists ( $hash_file );
	}

	if (empty($existentPDF)) {
		if ($box_number > 0 && $type > 0 && $year >= 0 && $year <= date ( 'Y' )) {
			$boxController = new BoxController ( $db );

			$box = $boxController->getBox ( $user->getClient (), $box_number );

			if ($box != NULL) {
				if (! $box->isSealed ()) {
					if (! $boxController->hasBooks ( $box )) {
						$typeController = new DocumentTypeController ( $db );
						$typeObj = $typeController->getTypeById ( $type ); // se !=null

						if (! empty ( $token )) {
							$folder = dirname ( __FILE__ ) . "/../../doc_files/$year/";
							if (! is_dir ( $folder ))
								mkdir ( $folder, 0777, true );

							$filename = sha1 ( $user->getClient () . "" . rand ( 1, 666 ) . sprintf ( "%04d%04d%s%s_%4d.pdf", $box_number, $number, strtoupper ( $letter ), ($volume > 0 ? "_VOL$volume" : ""), $year ) ) . ".pdf";

							if (rename ( $token, $folder . $filename )) {
								$document = new Document ();
								$document->setClient ( $user->getClient () );
								$document->setBox ( $box );
								$document->setType ( $type );
								$document->setYear ( $year );
								$document->setNumber ( $number );
								$document->setLetter ( $letter );
								$document->setVolume ( $volume );
								$document->setCompany ( $company );
								$document->setDate ( $date );
								$document->setFile ( "doc_files/$year/" . $filename );
								// $document->setPageCount(Utils::getPdfPageCount($document->getFile()));
								$document->setHash ( $hash_file );

								if (! $documentController->docExists ( $document )) {
									$id = $documentController->insertDocument ( $document, $user->getId () );
									if ($id > 0) {
										$response->ok = TRUE;
										$response->type = "success";
										if ($sealed)
											$boxController->sealBox ( $box->getId (), $sealed, $user->getId () );
									}
								} else {
									$response->type = "warning";
									$response->error = "Documento já cadastrado na caixa N&ordm; " . $document->getBox ()->getNumber ();
								}
							} else {
								$response->type = "error";
								$response->error = "Erro no arquivo enviado. Por favor tente novamente.";
							}
						} else {
							$document = new Document ();
							$document->setClient ( $user->getClient () );
							$document->setBox ( $box );
							$document->setType ( $type );
							$document->setYear ( $year );
							$document->setNumber ( $number );
							$document->setLetter ( $letter );
							$document->setVolume ( $volume );
							$document->setCompany ( $company );
							$document->setDate ( $date );

							if (! $documentController->docExists ( $document )) {
								$id = $documentController->insertDocument ( $document, $user->getId () );
								if ($id > 0) {
									$response->ok = TRUE;
									$response->type = "success";
									if ($sealed)
										$boxController->sealBox ( $box->getId (), $sealed, $user->getId () );
								}
							} else {
								$response->type = "warning";
								$response->error = "Documento já cadastrado na caixa N&ordm; " . $document->getBox ()->getNumber ();
							}
						}
					} else {
						$response->type = "warning";
						$response->error = "Caixa já cadastrada com livros. Somente cadastre livros nesta caixa.";
					}
				} else {
					$response->type = "warning";
					$response->error = "Caixa está selada";
				}
			} else {
				$response->type = "warning";
				$response->error = "Caixa não cadastrada";
			}
		} else {
			$response->error = "Parâmetros incorretos";
		}
	} else {
		$response->type = "warning";
		$response->error = "Este arquivo PDF já existe no servidor! <br>". $existentPDF->getType()->getDescription() . " Nº " . $existentPDF->getNumber() . "" . $existentPDF->getLetter() . "/" . $existentPDF->getYear() . 
		"<br>Caixa Nº " . $existentPDF->getBox()->getNumber();
		// Exclui o arquivo enviado
		unlink($token);
	}
	echo json_encode ( $response );
}