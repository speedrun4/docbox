<?php
namespace Docbox\tables;

include_once (dirname ( __FILE__ ) . "/../control/UserController.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
		'box_number'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "box_id";

/* DB table to use */
$sTable = "caixas";

/* Database connection information */
$userLogged = getUserLogged ();
$client = $userLogged->getClient ();
if ($userLogged == NULL || $client == 0)
	exit ();

$db = new DbConnection ();

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

/**
 * Ordering
 * $orderDirection a tabela possui somente uma coluna...
 */
$sOrder = "";
$orderDirection = "ASC";
if (isset ( $_POST ['iSortingCols'] )) {
	$sOrder = "ORDER BY  ";
	$arrOrder = is_array ( $_POST ['iSortingCols'] ) ? count ( $_POST ['iSortingCols'] ) : $_POST ['iSortingCols'];

	for($i = 0; $i < $arrOrder; $i ++) {
		if (isset ( $_POST ["sSortDir_$i"] )) {
			$dir = $_POST ["sSortDir_$i"];
			$column = $_POST ["iSortCol_$i"];

			if ($_POST ["bSortable_$column"] == "true") {
				$columnName = $aColumns [$column];

				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";

				$orderDirection = ($dir === 'asc' ? 'ASC' : 'DESC');
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

$sWhere = " WHERE box_dead = FALSE AND box_client = $client ";

if (isset ( $_POST ['doc_box'] ) && intval ( $_POST ['doc_box'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_box=" . intval ( $_POST ['doc_box'] );
}

$liv_box = getReqParam ( "liv_box", "int", "post" );
if ($liv_box > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "box_number=$liv_box";
}

$liv_number = getReqParam ( "liv_num", "int", "post" );
if ($liv_number > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}

	$sWhere .= "($liv_number BETWEEN doc_num_from AND doc_num_to)";
}

$liv_type = getReqParam ( "liv_type", "int", "post" );
if ($liv_type > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_type=$liv_type";
}

$liv_year = getReqParam ( "liv_year", "int", "post" );
if ($liv_year > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_year=$liv_year";
}

$liv_volume = getReqParam ( "liv_volume", "int", "post" );
if ($liv_volume > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_volume=$liv_volume";
}

$liv_location = getReqParam ( "liv_location", "int", "post" );
if ($liv_location != 0) {
	if ($liv_location == 1) { // Em estoque
		$sWhere .= " AND (box_request IS NULL OR box_request = 0) ";
	} else if ($liv_location == 2) { // Em pedido
		$sWhere .= " AND (box_request > 0) ";
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS box_number, " . str_replace ( " , ", " ", implode ( ", ", $aColumns ) ) . " FROM $sTable
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable $sWhere";
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

$boxIndex = 0;
$increment = $orderDirection == "ASC" ? 1 : - 1;

if ($aRow = mysqli_fetch_array ( $rResult )) {
	$boxIndex = $aRow ['box_number'];

	while ( $aRow = mysqli_fetch_array ( $rResult ) ) {
		if ($increment == 1) {
			while ( ++ $boxIndex != $aRow ['box_number'] ) {
				$row = array ();
				$row [] = $boxIndex;
				$output ['aaData'] [] = $row;
			}
		} else {
			while ( -- $boxIndex != $aRow ['box_number'] && $boxIndex > 0 ) {
				$row = array ();
				$row [] = $boxIndex;
				$output ['aaData'] [] = $row;
			}
		}
	}
}

echo json_encode ( $output );
?>