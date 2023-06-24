<?php
include_once (dirname(__FILE__) . "/Controller.php");
include_once (dirname(__FILE__) . "/../model/KeyValue.php");

use DocBox\model\KeyValue;
use Docbox\control\Controller;

class RequestStatusController extends Controller {
	/**
	 * @return KeyValue[]
	 */
	public function getRequestStatus() {
		$situations = array();
		$query = "SELECT * FROM status_pedidos WHERE sta_dead = FALSE";
		if($result = $this->db->query($query)) {
			while($row = $result->fetch_object()) {
				$situations[] = new KeyValue($row->sta_id, $row->sta_name);
			}
		}
		return $situations;
	}

	/**
	 * @return KeyValue[]
	 */
	public function getRequestStatusById($id) {
		$status = NULL;
		$query = "SELECT * FROM status_pedidos WHERE sta_id = $id AND sta_dead = FALSE";

		if($result = $this->db->query($query)) {
			if($row = $result->fetch_object()) {
				$status = new KeyValue($row->sta_id, $row->sta_name);
			}
		}

		return $status;
	}
}