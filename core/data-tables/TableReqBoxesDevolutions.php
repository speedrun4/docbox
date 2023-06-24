<?php
namespace Docbox\tables;
/**
* Exibe as devoluções ocorridas no pedido
* Tabela para modal de devoluções da página visualizar pedido de caixas
*/
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../model/Request.php");
include_once (dirname(__FILE__) . "/../model/RequestStatus.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../utils/Input.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image) */
$aColumns = array (
	'ret_id',
	'ret_number',
	'usr_name',
	'ret_creation_time',
	'ret_file'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "ret_id";

/* DB table to use */
$sTable = "devolucoes";

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use DateTime;
use Docbox\utils\Input;

$userLogged = getUserLogged();
if ($userLogged == NULL) exit();
$db = new DbConnection();

$req_id = Input::int('r');
$reqController = new RequestController($db);

$request = $reqController->getRequest($req_id);

if ($request == NULL || ($request->getClient() != $userLogged->getClient() && !$userLogged->isAdmin())) {
		exit();
}

/* If you just want to use the basic configuration for DataTables with PHP server-side, there is no need to edit below this line */

/* Local functions */
function fatal_error($sErrorMessage = '') {
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

	for ($i = 0; $i < $arrOrder; $i ++) {
		if (isset($_POST["sSortDir_$i"])) {
			$dir = $_POST["sSortDir_$i"];
			$column = $_POST["iSortCol_$i"];

			if ($_POST["bSortable_$column"] == "true") {
				$columnName = $aColumns[$column];

				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";
			}
		}
	}

	$sOrder = substr_replace($sOrder, "", - 2);
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

$sWhere = " WHERE dop_req = $req_id ";

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS DISTINCT " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable
		INNER JOIN caixas_devolucoes ON bre_return = ret_id
		INNER JOIN caixas_pedidas ON dop_id = bre_box_requested
		LEFT JOIN modificacoes ON mod_table like 'devolucoes' AND mod_tb_id = ret_id AND mod_action LIKE 'I'
		LEFT JOIN users on usr_id = ret_user
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable";
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
	for ($i = 0; $i < count($aColumns); $i ++) {
		if (strcasecmp("ret_creation_time", $aColumns[$i]) == 0) {
			$date = DateTime::createFromFormat("Y-m-d H:i:s", $aRow[$aColumns[$i]]);
			if ($date) {
				$row[] = $date->format("d/m/Y à\s H:i:s");
			} else {
				$row[] = '';
			}
		} else if ($aColumns[$i] != ' ') {
			$row[] = $aRow[$aColumns[$i]];
		}
	}
	$output['aaData'][] = $row;
}

echo json_encode($output);
