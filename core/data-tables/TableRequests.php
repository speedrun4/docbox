<?php
namespace Docbox\tables;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
		'req_id',
		'req_type_desc',
		'req_number',
		'req_date',
		'req_time',
		'usr_name',
		'sta_name',
		'req_type'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "req_id";

/* DB table to use */
$sTable = "pedidos";

/* Database connection information */
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../control/RequestController.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../utils/Input.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\getReqParam;
use DateTime;
use Docbox\utils\Input;

$userLogged = getUserLogged ();
if ($userLogged == NULL)
	exit ();
$db = new DbConnection ();

/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
 * no need to edit below this line
 */

/*
 * Local functions
 */
function fatal_error($sErrorMessage = '') {
	header ( $_SERVER ['SERVER_PROTOCOL'] . ' 500 Internal Server Error' );
	die ( $sErrorMessage );
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
	$arrOrder = is_array ( $_POST ['iSortingCols'] ) ? count ( $_POST ['iSortingCols'] ) : $_POST ['iSortingCols'];

	for($i = 0; $i < $arrOrder; $i ++) {
		if (isset ( $_POST ["sSortDir_$i"] )) {
			$dir = $_POST ["sSortDir_$i"];
			$column = $_POST ["iSortCol_$i"];

			if ($_POST ["bSortable_$column"] == "true") {
				$columnName = $aColumns [$column];

				if ($columnName == "req_time") {
					$columnName = " TIME(req_datetime) ";
				}

				if ($columnName == "req_date") {
					$columnName = " DATE(req_datetime) ";
				}

				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";
			}
		}
	}

	$sOrder = substr_replace ( $sOrder, "", - 2 );
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

/*
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
// $sWhere = "";
// if ( isset($_POST['search']) && $_POST['search']['value'] != "" ) {
// $sWhere = "WHERE (";
// $sWhere .= $aColumns[1]." LIKE '%".($_POST['search']['value'])."%' ";
// $sWhere .= ')';
// }

$sWhere = "";

if ($userLogged->getProfile () == User::USER_COMMON) {
	$sWhere = " WHERE req_client = " . $userLogged->getClient ();
}

$number = getReqParam ( "req_number", "int", 'post' );
if ($number > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "req_number = $number";
}
$filterDocuments = FALSE;
$doc_number = Input::int ( 'doc_number' );
if ($doc_number > 0) {
	$filterDocuments = TRUE;
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= " doc_number = $doc_number ";
}

$doc_year = Input::int ( 'doc_year' );
if ($doc_year > 0) {
	$filterDocuments = TRUE;
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= " doc_year = $doc_year ";
}

$req_user = getReqParam ( "req_user", "int", "post" );
if ($req_user != NULL) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= " mod_user = $req_user ";
}

if (isset ( $_POST ['req_status'] ) && intval ( $_POST ['req_status'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "req_status =" . intval ( $_POST ['req_status'] );
}

$openDevolutions = Input::int ( 'req_open_dev' );
if ($openDevolutions > 0) {
	if ($openDevolutions == 1) { // Pedido que possui devoluções em aberto
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " (SELECT count(*) FROM devolucoes WHERE ret_request = req_id AND ret_file IS NULL) > 0 ";
	} else if ($openDevolutions == 2) {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " (SELECT count(*) FROM devolucoes WHERE ret_request = req_id AND ret_file IS NOT NULL) > 0 ";
	}
}

$dateFrom = getReqParam ( "req_date_from", "str", "post" );
$dateTo = getReqParam ( "req_date_to", "str", "post" );

if (! empty ( $dateFrom )) {
	if ($dateFrom = DateTime::createFromFormat ( "Y-m-d", $dateFrom )) {
		if (! empty ( $dateTo )) {
			if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= " DATE_FORMAT(req_datetime, '%Y-%m-%d') BETWEEN '" . $dateFrom->format ( 'Y-m-d' ) . "' AND '" . $dateTo->format ( 'Y-m-d' ) . "'";
			}
		} else {
			if ($sWhere == "") {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			$sWhere .= " DATE_FORMAT(req_datetime, '%Y-%m-%d') = '" . $dateFrom->format ( 'Y-m-d' ) . "' ";
		}
	}
} else if (! empty ( $dateTo )) {
	if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " DATE_FORMAT(req_datetime, '%Y-%m-%d') <= '" . $dateTo->format ( 'Y-m-d' ) . "' ";
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . ($filterDocuments ? "DISTINCT" : "") . " req_id, if(req_type = 2, 'Documentos', 'Caixas') as req_type_desc, req_number, req_datetime, usr_name, status_pedidos.sta_name, req_type FROM $sTable
		LEFT JOIN status_pedidos ON req_status = sta_id 
		LEFT JOIN modificacoes on mod_table like 'pedidos' and mod_tb_id = req_id and mod_action like 'I' 
		LEFT JOIN users ON usr_id = req_user ";
if ($filterDocuments) {
	$sQuery .= "LEFT JOIN documentos_pedidos ON dcr_request = req_id 
		LEFT JOIN documentos ON doc_id = dcr_document ";
}
$sQuery .= "$sWhere
        $sOrder
        $sLimit";

$rResult = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error_: ' . mysqli_errno ( $db->con ) );

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_errno ( $db->con ) );
$aResultFilterTotal = mysqli_fetch_array ( $rResultFilterTotal );
$iFilteredTotal = $aResultFilterTotal [0];

/* Total data set length */
if ($userLogged->getProfile () == User::USER_COMMON) {
	$sWhere = " WHERE req_client = " . $userLogged->getClient ();
}
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable WHERE req_client = " . $userLogged->getClient ();
$rResultTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_errno ( $db->con ) );
$aResultTotal = mysqli_fetch_array ( $rResultTotal );
$iTotal = $aResultTotal [0];

/* Output */
$output = array (
		"sEcho" => isset ( $_POST ['sEcho'] ) ? intval ( $_POST ['sEcho'] ) : 0,
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array ()
);

while ( $aRow = mysqli_fetch_array ( $rResult ) ) {
	$row = array ();
	for($i = 0; $i < count ( $aColumns ); $i ++) {
		if (strcasecmp ( "req_date", $aColumns [$i] ) == 0) {
			$date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow ["req_datetime"] );
			$row [] = $date->format ( "d/m/Y" );
		} else if (strcasecmp ( "req_time", $aColumns [$i] ) == 0) {
			$date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow ["req_datetime"] );
			$row [] = $date->format ( "H:i" );
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