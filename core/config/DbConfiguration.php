<?php
function getDbConfig() {
	$database_config = array (
			1 => array (
					"host" => "127.0.0.1",
					"database" => "scando85_docbox",
					"user" => "root",
					"password" => "" 
			) 
	);
	
	if (isset ( $database_config [1] ))
		return $database_config [1];
	
	return NULL;
}