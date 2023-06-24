<?php
namespace Docbox\tables;

include_once (dirname ( __FILE__ ) . "/../model/User.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use DateTime;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
		'not_id',
		'not_tb_id',
		'cli_name',
		'usr_name',
		'not_when',
		'not_type',
		'not_event'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "not_id";

/* DB table to use */
$sTable = "notifications";

$userLogged = getUserLogged ();
$client = $userLogged->getClient ();
if ($userLogged == NULL || $client == 0)
	exit ();

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

$sWhere = "";


$dateFrom = getReqParam("txt_date_from", "str", "post");
$dateTo = getReqParam("txt_date_to", "str", "post");

if(!empty($dateFrom)) {
	if($dateFrom = DateTime::createFromFormat("Y-m-d", $dateFrom)) {
		if(!empty($dateTo)) {
			if($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
				if($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= " DATE_FORMAT(not_when, '%Y-%m-%d') BETWEEN '" . $dateFrom->format('Y-m-d') . "' AND '" . $dateTo->format('Y-m-d') . "'";
			}
		} else {
			if($sWhere == "") {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			$sWhere .= " DATE_FORMAT(not_when, '%Y-%m-%d') = '" . $dateFrom->format('Y-m-d') . "' ";
		}
	}
} else if(!empty($dateTo)) {
	if($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
		if($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " DATE_FORMAT(not_when, '%Y-%m-%d') <= '" . $dateTo->format('Y-m-d') . "' ";
	}
}

$type = getReqParam("txt_type", "int", 'post');
if($type > 0) {
	if($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "not_type = $type" ;
}

$client = getReqParam("txt_client", "int", 'post');
if($client > 0) {
	if($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "usr_client = $client" ;
}

$username = getReqParam ( "txt_user", "str", "post" );
if (! empty ( $username )) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "usr_name LIKE '%$username%'";
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace ( " , ", " ", implode ( ", ", $aColumns ) ) . " FROM $sTable 
	LEFT JOIN users on usr_id = not_user 
	LEFT JOIN clientes ON cli_id = usr_client 
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
		if (strcasecmp ( "not_when", $aColumns [$i] ) == 0) {
			if (! empty ( $aRow [$aColumns [$i]] )) {
				$date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow [$aColumns [$i]] );
				$row [] = $date->format ( "d/m/Y à\s H:i" );
			} else {
				$row [] = "";
			}
		} else if ($aColumns [$i] != '') {
			$row [] = utf8_encode ( $aRow [$aColumns [$i]] );
		}
	}
	$output ['aaData'] [] = $row;
}

echo json_encode ( $output );