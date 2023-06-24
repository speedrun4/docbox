<?php
namespace Docbox\control;

include_once(dirname(__FILE__) . "/../model/DbConnection.php");
include_once(dirname(__FILE__) . "/../model/Request.php");

use Docbox\model\DbConnection;

class ModificationController {
	/**
	 * Escreve no banco uma modificação
	 * @param DbConnection $con
	 * @param string $table
	 * @param integer $id
	 * @param string $action
	 * @param integer $user
	 * @return boolean
	 */
	public static function writeModification($con, $table, $id, $action, $user_id, $extra_info=NULL) {
		$query = "INSERT INTO modificacoes(mod_table, mod_tb_id, mod_action, mod_user, mod_info) VALUES(?,?,?,?,?)";
		if($stmt = $con->prepare($query)) {
		    if($stmt->bind_param("sisis", $table, $id, $action, $user_id, $extra_info)) {
				return $stmt->execute();
			}
		}
		return FALSE;
	}

	/**
	 * Escreve no banco uma modificação
	 * @param DbConnection $con
	 * @param string $table
	 * @param integer $id
	 * @param string $action
	 * @param integer $user
	 * @return boolean
	 */
	public static function writePastModification($con, $table, $id, $action, $user_id, $timestamp, $extra_info=NULL) {
		$query = "INSERT INTO modificacoes(mod_table, mod_tb_id, mod_action, mod_user, mod_when, mod_info) VALUES(?,?,?,?,?,?)";
		if($stmt = $con->prepare($query)) {
			if($stmt->bind_param("sisiss", $table, $id, $action, $user_id, $timestamp, $extra_info)) {
				return $stmt->execute();
			}
		}
		return FALSE;
	}
}