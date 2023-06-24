<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/Controller.php");
include_once (dirname ( __FILE__ ) . "/../model/Client.php");

use Docbox\model\Client;

class ClientController extends Controller {
	/**
	 * @return Client[]
	 */
	function getClients() {
		$clients = array();
		$query = "SELECT * FROM clientes";
		if($result = $this->db->query($query)) {
			while($row = $result->fetch_object()) {
				$clients[] = Client::withRow($row);
			}
		}
		return $clients;
	}

	/**
	 * @param integer $id
	 * @return Client|NULL
	 */
	function getClient($id) {
		$query = "SELECT * FROM clientes where cli_id = $id";
		if($result = $this->db->query($query)) {
			while($row = $result->fetch_object()) {
				return Client::withRow($row);
			}
		}
		return NULL;
	}
}