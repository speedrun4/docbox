<?php

namespace Docbox\tables;

include_once(dirname(__FILE__) . "/../control/UserSession.php");
include_once(dirname(__FILE__) . "/../model/DbConnection.php");
include_once(dirname(__FILE__) . "/../model/Withdrawal.php");
include_once(dirname(__FILE__) . "/../model/Devolution.php");
include_once(dirname(__FILE__) . "/../model/WithdrawalStatus.php");
include_once(dirname(__FILE__) . "/../utils/Input.php");
include_once(dirname(__FILE__) . "/../control/WithdrawalController.php");

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image) */
$aColumns = array(
	'mod_id',
	'usr_name',
	'mod_action',
	'mod_when',
	'mod_info'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "mod_id";

/* DB table to use */
$sTable = "modificacoes";

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\Modification;
use Docbox\model\RequestStatus;
use DateTime;
use Docbox\control\WithdrawalController;
use Docbox\utils\Input;

$userLogged = getUserLogged();
if ($userLogged == NULL) exit();
$db = new DbConnection();

$withdrawalID = Input::int("r");
$withController = new WithdrawalController($db);
$withdrawal = $withController->getWithdrawalById($withdrawalID, $userLogged->getClient ());

if ($withdrawal == NULL || $withdrawal->getClient() != $userLogged->getClient()) {
	exit();
}

/* If you just want to use the basic configuration for DataTables with PHP server-side, there is no need to edit below this line */

/* Local functions */
function fatal_error($sErrorMessage = '')
{
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
	die($sErrorMessage);
}

/*
 * Paging
 */
$sLimit = "";
if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
	$sLimit = "LIMIT " . intval($_POST['iDisplayStart']) . ", " . intval($_POST['iDisplayLength']);
}

/*
 * Ordering
 */
$sOrder = "";
if (isset($_POST['iSortingCols'])) {
	$sOrder = "ORDER BY  ";
	$arrOrder = $_POST['iSortingCols'];

	for ($i = 0; $i < $arrOrder; $i++) {
		if (isset($_POST["sSortDir_$i"])) {
			$dir = $_POST["sSortDir_$i"];
			$column = $_POST["iSortCol_$i"];

			if ($_POST["bSortable_$column"] == "true") {
				$columnName = $aColumns[$column];

				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";
			}
		}
	}

	$sOrder = substr_replace($sOrder, "", -2);
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

$sWhere = " WHERE mod_table like 'retiradas' AND mod_tb_id = " . $withdrawal->getId();

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable
		INNER JOIN users on usr_id = mod_user 
        INNER JOIN retiradas on pul_id = mod_tb_id 
        $sWhere
        $sOrder
        $sLimit";

$rResult = $db->query($sQuery) or fatal_error('MySQL Error_: ' . mysqli_errno($db->con));

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query($sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($db->con));
$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

/* Total data set length */
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable $sWhere";
$rResultTotal = $db->query($sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($db->con));
$aResultTotal = mysqli_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

/* Output */
$output = array(
	"sEcho" => isset($_POST['sEcho']) ? intval($_POST['sEcho']) : 0,
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

while ($aRow = mysqli_fetch_array($rResult)) {
	$row = array();
	for ($i = 0; $i < count($aColumns); $i++) {
		if (strcasecmp("mod_action", $aColumns[$i]) == 0) {
			if (strcasecmp($aRow[$aColumns[$i]], Modification::INSERT) == 0) {
				$row[] = "CADASTROU A RETIRADA";
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::CANCEL) == 0) {
				$row[] = "CANCELOU A RETIRADA";
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::UPDATE_RECEIPT) == 0) {
				if (is_file(dirname(__FILE__) . "/../../" . $aRow['mod_info'])) {
					$row[] = "<a href='" . $aRow['mod_info'] . "' target='_blank'>ALTEROU COMPROVANTE</a>";
				} else {
					$row[] = "ALTEROU COMPROVANTE";
				}
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::UPDATE2COMPLETED) == 0) {
				if (is_file(dirname(__FILE__) . "/../../" . $aRow['mod_info']))
					$row[] = "<a href='" . $aRow['mod_info'] . "' target='_blank'>FINALIZOU O PEDIDO</a>";
			} else if(strcasecmp($aRow[$aColumns[$i]], Modification::UPDATE) == 0) {
				$row[] = "ALTEROU A LISTA DE RETIRADA";
			} else {
				$row[] = "-";
			}
		} else if (strcasecmp("mod_when", $aColumns[$i]) == 0) {
			$date = DateTime::createFromFormat("Y-m-d H:i:s", $aRow[$aColumns[$i]]);
			if ($date) {
				$row[] = $date->format("d/m/Y à\s H:i:s");
			} else {
				$row[] = '';
			}
		} else if ($aColumns[$i] != ' ') {
			if (file_exists(dirname(__FILE__) . "/../../DEVMACHINE.inc")) {
				$row[] = utf8_encode($aRow[$aColumns[$i]]);
			} else {
				$row[] = $aRow[$aColumns[$i]];
			}
		}
	}
	$output['aaData'][] = $row;
}

echo json_encode($output);
