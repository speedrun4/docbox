<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/DocumentType.php");
include_once (dirname ( __FILE__ ) . "/ModificationController.php");

use Docbox\model\DocumentType;

class DocumentTypeController extends Controller {
	/**
	 * @param DocumentType $type
	 */
	public function insertType($type, $preffix, $user) {
		$ok = false;
		$this->db->begin();
		$query = "INSERT INTO tipos_documentos(dct_client, dct_name, dct_preffix) VALUES(" . $user->getClient() . ",'" . $type . "', '$preffix')";
		if($this->db->query($query)) {
			$id = mysqli_insert_id($this->db->con);
			$ok = $id > 0 && ModificationController::writeModification($this->db, "tipos_documentos", $id, "I", $user->id);
		}

		if($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
		}

		return $ok;
	}

	/**
	 * @param DocumentType $type
	 */
	public function updateType($id, $name, $preffix, $user) {
		$this->db->begin();
		$query = "UPDATE tipos_documentos SET dct_name=?, dct_preffix=? WHERE dct_id = ? and dct_dead=FALSE";
		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("ssi", $name, $preffix, $id)) {
				$ok = $stmt->execute() && ModificationController::writeModification($this->db, "tipos_docuemtos", $id, "U", $user);
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
	 * @param integer $type
	 * @return boolean
	 */
	public function deleteType($type, $user_id) {
		$this->db->begin();
		$query = "UPDATE tipos_documentos SET dct_dead = TRUE WHERE dct_id=?";

		if($stmt = $this->db->prepare($query)) {
			if($stmt->bind_param("i", $type)) {
				$ok = $stmt->execute() && ModificationController::writeModification($this->db, "tipos_documentos", $type, "D", $user_id);
			}
		}

		if($ok) {
			$this->db->commit();
		} else {
			$this->db->rollback();
		}

		return $ok;
	}

	public function getTypes($client) {
		$types = array();
		$query = "SELECT * FROM tipos_documentos WHERE dct_dead = FALSE and dct_client = $client ORDER BY dct_name";
		if($result = $this->db->query($query)) {
			while($row = $result->fetch_object()) {
				$types[] = DocumentType::withRow($row);
			}
		}
		return $types;
	}
	
	public function getTypeById($id) {
		$type = NULL;
		$query = "SELECT * FROM tipos_documentos WHERE dct_id= $id and dct_dead = FALSE";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				$type = DocumentType::withRow($row);
			}
		}
		return $type;
	}

	public function getTypeByPreffix($preffix, $client) {
		$type = NULL;
		$query = "SELECT * FROM tipos_documentos WHERE dct_preffix LIKE '$preffix' AND dct_client = $client AND dct_dead = FALSE";
		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				$type = DocumentType::withRow($row);
			}
		}
		return $type;
	}
}