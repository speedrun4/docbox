<?php
include_once (dirname ( __FILE__ ) . "/../model/Box.php");
include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/Modification.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");

use Docbox\control\Controller;
use Docbox\control\ModificationController;
use function Docbox\control\getUserLogged;
use Docbox\model\Box;
use Docbox\model\Modification;

class BoxController extends Controller {
    /**
     * @param integer $client
     * @param integer $number
     * @param integer $tower
     * @param integer $floor
     * @return boolean
     */
	function registerBox($client, $number, $department) {
		$ok = FALSE;
		$user = getUserLogged();
		if($user != NULL) {
			$this->db->begin();
			$query = "INSERT INTO caixas(box_number, box_client, box_department) VALUES(?,?,?)";
			if($stmt = $this->db->prepare($query)) {
				if($stmt->bind_param("iii", $number, $client, $department)) {
					if($stmt->execute()) {
						if($stmt->insert_id > 0) {
							if(ModificationController::writeModification($this->db, "caixas", $stmt->insert_id, "I", $user->id)) {
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
			}
		}

		return $ok;
	}

	function boxExists($client, $box_number) {
		$query = "SELECT * FROM caixas WHERE box_client = $client AND box_number = $box_number AND box_dead = FALSE";
		if($result = $this->db->query($query)) {
			if($result->fetch_object()) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param int $client
	 * @param int $box_number
	 * @return Box|NULL
	 */
	function getBox($client, $box_number) {
		$query = "SELECT * FROM caixas 
                LEFT JOIN departamentos d ON d.dep_id = box_department 
                LEFT JOIN pedidos ON box_request = req_id 
		        LEFT JOIN status_pedidos ON req_status = sta_id 
                LEFT JOIN users ON usr_id = req_user 
                WHERE box_number = $box_number AND box_client = $client AND box_dead = FALSE";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				return Box::withRow($row);
			}
		}
		return NULL;
	}
	
	function getBoxById($box_id) {
		$query = "SELECT * FROM caixas 
                    LEFT JOIN departamentos d ON d.dep_id = box_department 
                    LEFT JOIN pedidos ON box_request = req_id 
		            LEFT JOIN status_pedidos ON req_status = sta_id 
                    LEFT JOIN users ON usr_id = req_user 
                    WHERE box_id = $box_id";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				return Box::withRow($row);
			}
		}
		return NULL;
	}

	/**
	 * @param Box $box
	 */
	public function deleteBox($box_id, $user_id) {
		$ok = TRUE;
		$this->db->begin();
		$query = "UPDATE caixas SET box_dead = TRUE WHERE box_id = ?";
		if($stmt = $this->db->prepare($query)) {
		    if($stmt->bind_param("i", $box_id)) {
		        if($stmt->execute() && ModificationController::writeModification($this->db, "caixas", $box_id, "D", $user_id)) {
		            $query = "UPDATE documentos SET doc_dead = TRUE WHERE doc_box = ?";

		            if($stmt = $this->db->prepare($query)) {
		                if($stmt->bind_param("i", $box_id)) {
		                    if($stmt->execute()) {
            					$ok = TRUE;
		                    }
		                }
		            }
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
	
	/**
	 * @param Box $box
	 */
	function isBoxEmpty($box) {
	    return !($this->hasBooks($box) || $this->hasDocs($box));
	}

	/**
	 * @param Box $box
	 * @param Box $substituteBox
	 */
	function transferDocuments($box, $substituteBox, $user_id) {
	    $ok = FALSE;
	    $this->db->begin();
	    $query = "UPDATE documentos set doc_box = ? WHERE doc_box = ?";

	    if($stmt = $this->db->prepare($query)) {
	        if($stmt->bind_param("ii", $substituteBox->id, $box->id)) {
	            if($stmt->execute() && ModificationController::writeModification($this->db, "documentos", $box_id, Modification::TRANSFER_DOCS2BOX, $user_id, "$box->id _to_ $substituteBox->id")) {
	                $ok = TRUE;
	            }
	        }
	    }
	    if ($ok) {
	        $this->db->commit();
	    } else {
	        $this->db->rollback();
	    }
	    return $ok;
	}

	function transferDocumentList($box_from, $box_to, $documents, $user_id) {
	    $ok = TRUE;
	    $this->db->begin();
	    foreach ($documents as $doc) {
	        $query = "UPDATE documentos SET doc_box = $box_to WHERE doc_box = $box_from AND doc_id = $doc";
	        $ok = $ok && $this->db->query($query) && ModificationController::writeModification($this->db, "documentos", $doc, Modification::TRANSFER_ONEDOC, $user_id, "$box_from _to_ $box_to");
	    }
	    if ($ok) {
	        $this->db->commit();
	    } else {
	        $this->db->rollback();
	    }
	    return $ok;
	}

	public function hasBooks($box) {
	    $query = "SELECT count(*) as count from documentos where doc_box = " . $box->getId() . " and doc_book = true and doc_dead = FALSE";
	    if($result = $this->db->query($query)) {
	        if($row = $result->fetch_object()) {
	            return intval($row->count) > 0;
	        }
	    }
	    return FALSE;
	}

	public function hasDocs($box) {
	    $query = "select count(*) as count from documentos where doc_box = " . $box->getId() . " and doc_book = FALSE and doc_dead = FALSE";
	    if($result = $this->db->query($query)) {
	        if($row = $result->fetch_object()) {
	            return intval($row->count) > 0;
	        }
	    }
	    return FALSE;
	}

    /**
     *
     * @param int $box_id
     * @param bool $seal
     * @param int $user_id
     */
    function sealBox($box_id, $seal, $user_id) {
        $ok = FALSE;
        $this->db->begin();
        $query = "update caixas set box_sealed = ? where box_id = ?";

        if ($stmt = $this->db->prepare($query)) {
            if ($stmt->bind_param("ii", $seal, $box_id)) {
                if ($stmt->execute() && ModificationController::writeModification($this->db, "caixas", $box_id, Modification::UPDATE, $user_id)) {
                    $ok = TRUE;
                }
            }
        }

        if ($ok) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }
        return $ok;
    }

    function getLastBoxTakenByUser($user) {
        $query = "SELECT doc_box FROM modificacoes 
        INNER JOIN documentos ON doc_id = mod_tb_id 
        INNER JOIN caixas ON box_id = doc_box 
        WHERE mod_user = $user AND mod_table LIKE 'documentos' and mod_action LIKE 'I' and box_dead = FALSE 
        ORDER BY mod_when DESC LIMIT 1;";

        if ($result = $this->db->query($query)) {
            if ($row = $result->fetch_object()) {
                return $this->getBoxById($row->doc_box);
            }
        }
        return NULL;
    }
}