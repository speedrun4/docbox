<?php
namespace Docbox\control;

include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");

use Docbox\model\DbConnection;

class Controller {
	/**
	 * @var DbConnection
	 */
	var $db = NULL;

	public function __construct($db) {
		$this->db = $db;
	}
}