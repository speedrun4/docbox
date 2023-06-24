<?php
namespace Docbox\tables;

include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
		'doc_id',
		'doc_id',
		'doc_box',
		'box_number',
		'doc_type',
		'doc_number',
		'doc_year',
		'doc_letter',
		'doc_volume',
		'doc_request'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "doc_id";

/* DB table to use */
$sTable = "documentos";

/* Database connection information */

$userLogged = getUserLogged ();
$client = $userLogged->getClient ();
if ($userLogged == NULL || $client == 0) {
	exit ();
}

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

$sWhere = " WHERE doc_dead=FALSE AND doc_client = " . $client . " ";

if (isset ( $_POST ['marks'] ) && is_array ( $_POST ['marks'] ) && count ( $_POST ['marks'] ) > 0) {
	$marks = $_POST ['marks'];
	$marksLength = count ( $_POST ['marks'] );
	$sWhere .= " AND (";
	for($i = 0; $i < $marksLength; $i ++) {
		$sWhere .= " doc_id = " . intval ( $marks [$i] );
		if (($i + 1) < $marksLength)
			$sWhere .= " OR ";
	}
	$sWhere .= ") ";
}

if (isset ( $_POST ['except'] ) && is_array ( $_POST ['except'] ) && count ( $_POST ['except'] ) > 0) {
	$marks = $_POST ['except'];
	$marksLength = count ( $_POST ['except'] );
	$sWhere .= " AND (";
	for($i = 0; $i < $marksLength; $i ++) {
		$sWhere .= " doc_id <> " . intval ( $marks [$i] );
		if (($i + 1) < $marksLength)
			$sWhere .= " AND ";
	}
	$sWhere .= ") ";
}

if (isset ( $_POST ['doc_box'] ) && intval ( $_POST ['doc_box'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_box=" . intval ( $_POST ['doc_box'] );
}

if (isset ( $_POST ['doc_number'] ) && intval ( $_POST ['doc_number'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_number=" . intval ( $_POST ['doc_number'] );
}

if (isset ( $_POST ['doc_year'] ) && intval ( $_POST ['doc_year'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_year=" . intval ( $_POST ['doc_year'] );
}

if (isset ( $_POST ['doc_type'] ) && intval ( $_POST ['doc_type'] ) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_type=" . intval ( $_POST ['doc_type'] );
}

$letter = getReqParam ( "doc_letter", "str", "post" );

if (! empty ( $letter )) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_letter LIKE '$letter'";
}

if (isset ( $_POST ['doc_location'] ) && intval ( $_POST ['doc_location'] ) != 0) {
	$location = intval ( $_POST ['doc_location'] );
	if ($location == 1) { // Em estoque
		$sWhere .= " AND (doc_request IS NULL OR req_status = 2 OR req_status = 5) ";
	} else if ($location == 2) { // Em pedido
		$sWhere .= " AND (req_status = 1 OR req_status = 3 OR req_status = 4) ";
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
$sQuery = "
        SELECT SQL_CALC_FOUND_ROWS req_status, dct_name, " . str_replace ( " , ", " ", implode ( ", ", $aColumns ) ) . " FROM $sTable  
		LEFT JOIN tipos_documentos on doc_type = dct_id 
		LEFT JOIN pedidos ON doc_request = req_id 
		LEFT JOIN status_pedidos ON req_status = sta_id 
		LEFT JOIN caixas ON doc_box = box_id 
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable where doc_dead=false";
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
		if (strcasecmp ( "doc_type", $aColumns [$i] ) == 0) {
			$row [] = utf8_encode ( $aRow ["dct_name"] );
		} else if (strcasecmp ( "doc_request", $aColumns [$i] ) == 0) {
			if ($aRow ['doc_request'] != NULL) {
				if ($aRow ['req_status'] == NULL || $aRow ['req_status'] == 2 || $aRow ['req_status'] == 5) {
					$row [] = "0";
				} else {
					$row [] = $aRow ['doc_request'];
				}
			} else {
				$row [] = "0";
			}
		} else if ($aColumns [$i] != ' ') {
			/* General output */
			$row [] = $aRow [$aColumns [$i]];
		}
	}
	$output ['aaData'] [] = $row;
}

echo json_encode ( $output );
