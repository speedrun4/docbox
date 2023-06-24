<?php
namespace Docbox\tables;

include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../control/UserController.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../utils/Input.php");
include_once (dirname ( __FILE__ ) . "/../model/User.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use Docbox\utils\Input;
use function Docbox\utils\getReqParam;
use DateTime;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
	'ret_id',
	'ret_number',
	'usr_name',
	'ret_creation_time',
	'ret_file',
	'ret_req_type'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "ret_id";

/* DB table to use */
$sTable = "devolucoes";

$client = 0;
$userLogged = getUserLogged ();

if ($userLogged) {
	$client = $userLogged->getClient ();
} else {
	$client = Input::int ( 'c' );
}

if ($client == 0)
	die ();

$db = new DbConnection ();

/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there
 * is no need to edit below this line
 */

/*
 * Local functions
 */
function fatal_error($sErrorMessage = '') {
	header ( $_SERVER ['SERVER_PROTOCOL'] . ' 500 Internal Server Error' );
	die ( $sErrorMessage );
}
function addWhere($sWhere) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	return $sWhere;
}

/*
 * Paging
 */
$sLimit = "";
if (isset ( $_POST ['iDisplayStart'] ) && $_POST ['iDisplayLength'] != '-1') {
	$sLimit = "LIMIT " . intval ( $_POST ['iDisplayStart'] ) . ", " . intval ( $_POST ['iDisplayLength'] );
}

/*
 * Ordering
 */
$sOrder = "";
if (isset ( $_POST ['iSortingCols'] )) {
	$sOrder = "ORDER BY  ";
	$arrOrder = $_POST ['iSortingCols'];

	for($i = 0; $i < $arrOrder; $i ++) {
		if (isset ( $_POST ["sSortDir_$i"] )) {
			$dir = $_POST ["sSortDir_$i"];
			$column = $_POST ["iSortCol_$i"];

			if ($_POST ["bSortable_$column"] == "true") {
				$columnName = $aColumns [$column];

				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";
			}
		}
	}

	$sOrder = substr_replace ( $sOrder, "", - 2 );
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

$sWhere = "";

if ($userLogged->getProfile () == User::USER_COMMON) {
	$sWhere = " WHERE doc_client = " . $userLogged->getClient ();
}

$status = Input::int ( "dev_status" );
if ($status > 0) {
	if ($status == 1) { // Em aberto
		$sWhere = addWhere ( $sWhere ) . " ret_file IS NULL";
	} else if ($status == 2) {
		$sWhere = addWhere ( $sWhere ) . " ret_file IS NOT NULL";
	}
}

$dev_number = Input::int ( 'dev_number' );
if ($dev_number > 0) {
	$sWhere = addWhere ( $sWhere ) . " ret_number = $dev_number";
}

$doc_number = Input::int ( 'doc_number' );
if ($doc_number > 0) {
	$sWhere = addWhere ( $sWhere ) . " doc_number = $doc_number";
}

$req_number = Input::int ( 'req_number' );
if ($req_number > 0) {
	$sWhere = addWhere ( $sWhere ) . " req_number = $req_number";
}

if (isset ( $_POST ['doc_title'] ) && ! empty ( $_POST ['doc_title'] )) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_company LIKE '%" . ($_POST ['doc_title']) . "%' ";
}

$dateFrom = Input::str ( "dev_date_from" );
$dateTo = Input::str ( "dev_date_to" );

if (! empty ( $dateFrom )) {
	if ($dateFrom = DateTime::createFromFormat ( "Y-m-d", $dateFrom )) {
		if (! empty ( $dateTo )) {
			if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
				$sWhere = addWhere ( $sWhere ) . " ret_creation_time BETWEEN '" . $dateFrom->format ( 'Y-m-d' ) . "' AND '" . $dateTo->format ( 'Y-m-d' ) . "'";
			}
		} else {
			$sWhere = addWhere() . " ret_creation_time = '" . $dateFrom->format ( 'Y-m-d' ) . "' ";
		}
	}
} else if (! empty ( $dateTo )) {
	if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
		$sWhere = addWhere ( $sWhere ) . " ret_creation_time <= '" . $dateTo->format ( 'Y-m-d' ) . "' ";
	}
}

$user = Input::int('dev_user');
if($user > 0) {
	$sWhere = addWhere($sWhere) . " usr_id = $user";
}

$doc_location = getReqParam ( 'doc_location', 'int', 'post' );
if ($doc_location != 0) {
	if ($doc_location == 1) { // Em estoque
		$sWhere .= " AND ((box_request IS NULL OR box_request = 0) AND (doc_request IS NULL OR doc_request = 0)) ";
	} else if ($doc_location == 2) { // Em pedido
		$sWhere .= " AND (box_request > 0 OR doc_request > 0) ";
	}
}

/* Individual column filtering */
for($i = 0; $i < count ( $aColumns ); $i ++) {
	if (isset ( $_POST ['bSearchable_' . $i] ) && $_POST ['bSearchable_' . $i] == "true" && $_POST ['sSearch_' . $i] != '') {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns [$i] . " LIKE '%" . mysql_real_escape_string ( $_POST ['sSearch_' . $i] ) . "%' ";
	}
}

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS DISTINCT " . str_replace ( " , ", " ", implode ( ", ", $aColumns ) ) . " FROM $sTable
		LEFT JOIN users on usr_id = ret_user
		LEFT JOIN documentos_devolucoes ON dre_return = ret_id
		LEFT JOIN documentos_pedidos ON dre_doc_requested = dcr_id
		LEFT JOIN documentos ON doc_id = dcr_document
		LEFT JOIN pedidos ON req_id = dcr_request
        $sWhere
        $sOrder
        $sLimit";

$rResult = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error_: ' . mysqli_errno ( $db->con ) );

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_errno ( $db->con ) );
$aResultFilterTotal = mysqli_fetch_array ( $rResultFilterTotal );
$iFilteredTotal = $aResultFilterTotal [0];

/* Total data set length */
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable";
$rResultTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_errno ( $db->con ) );
$aResultTotal = mysqli_fetch_array ( $rResultTotal );
$iTotal = $aResultTotal [0];

/*
 * Output
 */
$output = array (
		"sEcho" => isset ( $_POST ['sEcho'] ) ? intval ( $_POST ['sEcho'] ) : 0,
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array ()
);

while ( $aRow = mysqli_fetch_array ( $rResult ) ) {
	$row = array ();
	for($i = 0; $i < count ( $aColumns ); $i ++) {
		if (strcasecmp ( "ret_creation_time", $aColumns [$i] ) == 0) {
			$date = "";
			if ($aRow ['ret_creation_time'] != NULL) {
				if ($date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow ['ret_creation_time'] )) {
					$date = $date->format ( "d/m/Y à\s H:i:s" );
				}
			}
			$row [] = $date;
		} else if ($aColumns [$i] != ' ') {
			if (file_exists ( dirname ( __FILE__ ) . "/../../DEVMACHINE.inc" )) {
				$row [] = utf8_encode ( $aRow [$aColumns [$i]] );
			} else {
				$row [] = $aRow [$aColumns [$i]];
			}
		}
	}
	$output ['aaData'] [] = $row;
}

echo json_encode ( $output );
