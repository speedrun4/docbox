<?php
namespace Docbox\tables;

include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../model/Request.php");
include_once (dirname(__FILE__) . "/../model/Devolution.php");
include_once (dirname(__FILE__) . "/../model/RequestStatus.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

/* Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image) */
$aColumns = array (
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

use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\Devolution;
use Docbox\model\Modification;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;
use DateTime;

$userLogged = getUserLogged();
if ($userLogged == NULL) exit();
$db = new DbConnection();

$req_id = getReqParam("r", "int", "post");
$reqController = new RequestController($db);

$request = $reqController->getRequest($req_id);

if ($request == NULL || $request->getClient() != $userLogged->getClient()) {
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

$sWhere = " WHERE mod_table like 'pedidos' AND mod_tb_id = " . $request->getId();

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . ", req_receipt_file_1, req_receipt_file_2, ret_id, ret_number, ret_file FROM $sTable
		INNER JOIN users on usr_id = mod_user 
        INNER JOIN pedidos on req_id = mod_tb_id 
		LEFT JOIN devolucoes ON ret_id = mod_info
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
	for ($i = 0; $i < count($aColumns); $i ++) {
		if (strcasecmp("mod_action", $aColumns[$i]) == 0) {
			if (strcasecmp($aRow[$aColumns[$i]], "i") == 0) {
				$row[] = "CADASTROU O PEDIDO";
			} else if (strcasecmp($aRow[$aColumns[$i]], "U2" . RequestStatus::SENT) == 0) {
				$row[] = "PEDIDO SEPARADO PARA ENVIO";
			} else if (strcasecmp($aRow[$aColumns[$i]], "U2" . RequestStatus::CANCELED) == 0) {
				$row[] = "CANCELOU O PEDIDO";
			} else if (strcasecmp($aRow[$aColumns[$i]], "U2" . RequestStatus::ATTENDEND) == 0) {
				$row[] = "<a href='./request_files/". $aRow['req_receipt_file_1'] ."' target='_blank'>PEDIDO ENTREGUE</a>";
			} else if (strcasecmp($aRow[$aColumns[$i]], "U2" . RequestStatus::RETURNED) == 0) {
				$row[] = "SOLICITOU DEVOLUÇÃO";
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::UPDATE2RETURNING) == 0) {
				$row[] = "SOLICITOU DEVOLUÇÃO";
			} else if(strcasecmp($aRow[$aColumns[$i]], Modification::DELETE_DEVOLUTION_FILE) == 0) {
				$row[] = "EXCLUIU COMPROVANTE DE DEVOLUÇÃO (Nº " . $aRow['ret_number'] . ")";
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::FINISH_DEVOLUTION) == 0) {
				if(!empty($aRow['ret_file']) && file_exists(dirname(__FILE__) . "/../../request_files/" . $aRow['ret_file'])) {
					$row[] = "<a href='./request_files/". $aRow['ret_file'] ."' target='_blank'>FINALIZOU DEVOLUÇÃO (Nº" . $aRow['ret_number'] . ")</a>";
				} else {
					$row[] = "FINALIZOU DEVOLUÇÃO (Nº" . $aRow['ret_number'] . ")";
				}
			} else if (strcasecmp($aRow[$aColumns[$i]], Modification::UPDATE2COMPLETED) == 0) {
				if(!empty($aRow['req_receipt_file_2'])) {
					$row[] = "<a href='./request_files/". $aRow['req_receipt_file_2'] ."' target='_blank'>FINALIZOU O PEDIDO</a>";
				} else {
					$row[] = "FINALIZOU O PEDIDO";
				}
			} else {
				$row[] = $aRow[$aColumns[$i]];
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