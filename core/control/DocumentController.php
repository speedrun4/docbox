<?php
namespace Docbox\control;

include_once(dirname(__FILE__) . "/ModificationController.php");
include_once(dirname(__FILE__) . "/../model/Document.php");
include_once(dirname(__FILE__) . "/../model/DocumentType.php");
include_once(dirname(__FILE__) . "/../model/Modification.php");
include_once(dirname(__FILE__) . "/../utils/Utils.php");
include_once(dirname(__FILE__) . "/../utils/Result.php");
include_once(dirname(__FILE__) . "/Controller.php");
include_once(dirname(__FILE__) . "/DocumentTypeController.php");


use Docbox\control\Controller;
use Docbox\control\DocumentTypeController;
use Docbox\control\ModificationController;
use Docbox\model\Box;
use Docbox\model\Document;
use Docbox\model\DocumentType;
use Docbox\model\Modification;
use Docbox\utils\Result;

use function Docbox\utils\cleanSpeciallChars;

class DocumentController extends Controller {
	/**
	 * @param Document $doc
	 */
	public function insertDocument($doc, $user_id) {
		$id = 0;
		$ok = FALSE;
		$this->db->begin();
		$query = "INSERT INTO documentos(doc_client, doc_box, doc_number, doc_year, doc_type, doc_letter, doc_volume, doc_file, doc_hash, doc_company, doc_date) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
		if($stmt = $this->db->prepare($query)) {
			$date = NULL;
			if($doc->getDate() != NULL) {
				$date = $doc->getDate()->format("Y-m-d");
			}
			if($stmt->bind_param("iiiiisissss", $doc->client ,$doc->box->id, $doc->number, $doc->year, $doc->type, $doc->letter, $doc->volume, $doc->file, $doc->hash, $doc->company, $date)) {
				if($stmt->execute()) {
					$id = $stmt->insert_id;
					if($id != 0) {
						if(ModificationController::writeModification($this->db, "documentos", $id, "I", $user_id)) {
							$ok = TRUE;
						}
					}
				}
			}
		}

		if($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
			$id = 0;
		}

		return $id;
	}
	/**
	 * @param Document $doc
	 * @return boolean
	 */
	public function updateDocument($doc, $user_id) {
		$query = "update documentos set doc_box=?, doc_number=?, doc_year=?, doc_type=?, doc_letter=?, doc_volume=?, doc_company=?, doc_date=? where doc_id = ?";
		if($stmt = $this->db->prepare($query)) {
			$date = NULL;
			if($doc->getDate() != NULL) {
				$date = $doc->getDate()->format("Y-m-d");
			}
			if($stmt->bind_param("iiiisissi", $doc->box->id, $doc->number, $doc->year, $doc->type, $doc->letter, $doc->volume, $doc->company, $date, $doc->id)) {
				if($stmt->execute() && ModificationController::writeModification($this->db, "documentos", $doc->id, "U", $user_id)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	public function updateDocFile($doc_file, $doc_hash, $doc_id, $user_id) {
		$query = "UPDATE documentos SET doc_file=?, doc_hash=? where doc_id = ?";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("ssi", $doc_file, $doc_hash, $doc_id)) {
				if($stmt->execute() && ModificationController::writeModification($this->db, "documentos", $doc_id, Modification::UPDATE_DOCFILE, $user_id)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * @param Document $doc
	 */
	public function deleteDocument($doc_id, $user_id) {
		$ok = TRUE;
		$this->db->begin();
		$query = "update documentos set doc_dead = true WHERE doc_id = ?";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("i", $doc_id)) {
				if($stmt->execute() && ModificationController::writeModification($this->db, "documentos", $doc_id, "D", $user_id)) {
					$ok = TRUE;
				}
			}
		}

		if($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
		}

		return $ok;
	}

	public function deleteFile($doc, $user_id) {
	    $ok = FALSE;
	    $this->db->begin();
        $query = "UPDATE documentos SET doc_file = NULL, doc_hash = NULL WHERE doc_id = ?";

	    if($doc != NULL && !empty($doc->getFile())) {
	        if($stmt = $this->db->prepare($query)) {
	            if($stmt->bind_param("i", $doc->id)) {
	                if($stmt->execute() && ModificationController::writeModification($this->db, "documentos", $doc->id, Modification::DELETE_DOCFILE, $user_id)) {
            	        $filename = dirname(__FILE__) . "/../../" . $doc->getFile();

            	        if(file_exists($filename)) {
            	            if(unlink($filename)) {
            	                $ok = TRUE;
            	            }
            	        } else {
            	            $ok = TRUE;
            	        }
	                }
	            }
	        }

    	    if($ok) {
    	        $this->db->commit();
    	    } else {
    	        $this->db->rollback();
    	    }
	    }

	    return $ok;
	}

	public function getDocumentById($id) {
		$query = "SELECT * FROM documentos 
				LEFT JOIN tipos_documentos ON doc_type = dct_id 
				LEFT JOIN caixas on box_id = doc_box 
				LEFT JOIN departamentos d ON d.dep_id = box_department 
				LEFT JOIN pedidos p ON box_request = p.req_id 
				LEFT JOIN users u ON u.usr_id = p.req_user 
				WHERE doc_id = $id ";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				$doc = Document::withRow($row);
			}
		}
		return $doc;
	}

	/**
	 * 
	 * @param Document $doc
	 * @return boolean
	 */
	public function docExists($doc) {
		$query = "SELECT * FROM documentos " .
		"LEFT JOIN caixas on box_id = doc_box " .
		"LEFT JOIN departamentos d ON d.dep_id = box_department " .
		"WHERE doc_client = " . $doc->getClient() . " AND " .
		"doc_box = " . $doc->getBox()->getId() . " AND " .
		"doc_year = " . $doc->year . " AND " .
		"doc_dead = FALSE AND " .
		"doc_number = " . $doc->getNumber() . " AND " .
		"doc_type = " . $doc->type . " AND " .
// 		($doc->number == NULL ? "doc_number = 0" : "doc_number = " . $doc->number . "" ) . 
		(empty($doc->letter) ? "doc_letter IS NULL" : "doc_letter like '" . $doc->letter . "'" ) . 
		($doc->volume > 0 ? " AND doc_volume = " . $doc->getVolume() : " AND doc_volume IS NULL");

		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				$doc->setBox(Box::withRow($row));
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param Document $doc
	 * @return boolean
	 */
	public function existsAnother($doc) {
		$query = "SELECT * FROM documentos " .
			"LEFT JOIN caixas on box_id = doc_box " .
			"LEFT JOIN departamentos d ON d.dep_id = box_department " .
			"WHERE doc_client = " . $doc->getClient() . " AND " .
			"doc_id <> " . $doc->getId() . " AND " .
			"doc_box = " . $doc->getBox()->getId() . " AND " .
			"doc_year = " . $doc->year . " AND " .
			"doc_dead = FALSE AND " .
			"doc_number = " . $doc->getNumber() . " AND " .
			"doc_type = " . $doc->type . " AND " .
		($doc->letter == NULL ? "doc_letter IS NULL" : "doc_letter like '" . $doc->letter . "'" ) .
		($doc->volume > 0 ? " AND doc_volume = " . $doc->getVolume() : " AND doc_volume IS NULL");

		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function findTitles($title) {
		$titles = array();
		if(!empty($title)) {
			$title = trim(cleanSpeciallChars($title));
			$cTitle = $cTitle = explode(' ', $title);

			$naturalQuery = "SELECT distinct doc_company
				FROM documentos 
				WHERE
					MATCH (doc_company) AGAINST ('$title' IN NATURAL LANGUAGE MODE) 
				ORDER by"
				.(! empty($cTitle) ? " IF(doc_company RLIKE '^".$cTitle[0]."', 1, 2)," : "").
					"MATCH (doc_company) AGAINST ('$title' IN NATURAL LANGUAGE MODE) desc,".
					"doc_company LIMIT 20;";

			if(mb_strlen($title) > 4) {
				if($result = $this->db->query($naturalQuery)) {
					while($row = $result->fetch_object()) {
						$found  = new stdClass();
						$found->value = ($row->doc_company);
						$titles[] = $found;
					}
				}
			}

			if(count($titles) == 0) {
				$likeQuery = "SELECT DISTINCT doc_company 
							FROM documentos 
							WHERE doc_company LIKE '%$title%' 
							ORDER BY IF(doc_company RLIKE '^$title', 1, 2), doc_company ASC LIMIT 20";
				if($result = $this->db->query($likeQuery)) {
					while($row = $result->fetch_object()) {
						$found  = new stdClass();
						$found->value = ($row->doc_company);
						$titles[] = $found;
					}
				}
			}
		}
		return $titles;
	}

	public function docFileExists($hash) {
		$query = "SELECT * FROM documentos WHERE doc_hash LIKE '$hash' and doc_dead = FALSE";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				return $this->getDocumentById($row->doc_id);
			}
		}
		return NULL;
	}

	/**
	 * @param int $box_id
	 * @param string $filename Nome do arquivo no padrão [Prefix][Número][Letra_opcional]_[Ano]_VOL[Numero do Volume].pdf, p.ex.: DOC0011S_2020_VOL1.pdf, DOC0011_2020.pdf
	 * @param int $client_id
	 */
	public function getDocumentByBoxFilename($box_id, $filename, $client_id) {
		$result = new Result();
		$pattern = "/[a-zA-Z]{" . DocumentType::PREFFIX_LENGTH . "}\d{4,}([a-zA-Z]+)*_\d{4}(_VOL\d+)*.pdf/";
		if(preg_match($pattern, $filename)) {
			$docTypeController = new DocumentTypeController($this->db);

			// Prefixo
			$preffix = substr($filename, 0, 3);
	
			// Prefixo -> tipo
			$docType = $docTypeController->getTypeByPreffix($preffix, $client_id);
			if($docType == NULL) {
				$result->setOK(false);
				$result->putMessage("Tipo de documento não encontrado. Verifique o prefixo do arquivo enviado.");
				return $result;
			}

			// Número
			$parts = explode("_", substr($filename, 3));
			$number = intval(preg_replace("/[a-zA-Z]/", "", $parts[0]));
			if($number <= 0) {
				$result->setOK(false);
				$result->putMessage("O número do documento deve ser maior que 0");
				return $result;
			}

			// Letra, se tiver
			$letter = preg_replace("/[^a-zA-Z]/", "", $parts[0]);
			if(!empty($letter)) {
				$letter = $letter[0];
			} else {
				$letter = NULL;
			}
	
			// Ano
			$year = intval(substr($parts[1], 0, 4));
			if($year <= 1900) {
				$result->setOK(false);
				$result->putMessage("O ano do documento deve ser maior que 1900");
				return $result;
			}
	
			// Volume, se tiver
			$volume = 0;
			if(count($parts) == 3) {
				$volume = intval(preg_replace("/[^0-9]/", "", $parts[2]));
			}

			if($box_id <= 0) {
				$result->setOK(false);
				$result->putMessage("Caixa inválida");
				return $result;
			}
	
			if($docType != NULL && $client_id > 0) {
				$query = "SELECT * FROM documentos " .
						" INNER JOIN tipos_documentos ON dct_id = doc_type " .
						"LEFT JOIN caixas on box_id = doc_box " .
						"LEFT JOIN departamentos d ON d.dep_id = box_department " .
						"WHERE doc_client = $client_id AND " .
						"doc_box = $box_id AND " .
						"doc_year = $year AND " .
						"doc_dead = FALSE AND " .
						"doc_number = $number AND " .
						"doc_type = " . $docType->id . " AND " .
				(empty($letter) ? "doc_letter IS NULL" : "doc_letter like '" . $letter . "'" ) .
				($volume > 0 ? " AND doc_volume = $volume" : " AND doc_volume IS NULL");
				
				if($qResult = $this->db->query($query)) {
					if($row = $qResult->fetch_object()) {
						$doc = Document::withRow($row);
						$result->setOk(true);
						$result->setResult($doc);
					} else {
						$result->putMessage("Documento não encontrado na caixa!");
					}
				}
			}
		}

		return $result;
	}
}