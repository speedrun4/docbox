<?php
namespace Docbox\model;

use mysqli;
use mysqli_stmt;

include_once (dirname(__FILE__) . "/../config/DbConfiguration.php");

class DbConnection {
	/**
	 * @var mysqli
	 */
    var $con;

    function __construct() {
    	$config = getDbConfig();
        $this->con = new mysqli($config["host"], $config["user"], $config["password"], $config["database"]);

        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    }

	/**
	 * @param string $query
	 * @return mysqli_stmt
	 */
    function prepare($query) {
        return $this->con->prepare($query);
    }

    /**
     * @param string $query
     */
    function query($query) {
        return $this->con->query($query);
    }

    function insert($query) {
    	if($this->query($query)) {
    		return $this->con->insert_id;
    	}
    	return 0;
    }

    function close() {
        $this->con->close();
    }
    /**
     * Inicia uma transação
     */
    public function begin() {
    	$this->con->query("SET autocommit=0");
    	$this->con->query("BEGIN");
    }
    /**
     * Comita uma transação
     */
    public function commit() {
    	$this->con->query("COMMIT");
    }
    /**
     * Cancela uma transação
     */
    public function rollback() {
    	$this->con->query("ROLLBACK");
    }
}
