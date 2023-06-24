<?php
namespace Docbox\tables;

/* Database connection information */
include_once (dirname ( __FILE__ ) . "/../control/RequestController.php");
include_once (dirname ( __FILE__ ) . "/../control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/../model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/../utils/Utils.php");
include_once (dirname ( __FILE__ ) . "/../utils/Input.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use Docbox\model\User;
use DateTime;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
	'pul_id',
	'pul_number',
	'usr_name',
	'pul_dt_creation',
	'pul_dt_withdrawal',
	'pul_status'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "pul_id";

/* DB table to use */
$sTable = "retiradas";

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
				
				$sOrder .= $columnName . " " . ($dir === 'asc' ? 'ASC' : 'DESC') . ", ";
			}
		}
	}

	$sOrder = substr_replace ( $sOrder, "", - 2 );
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

$sWhere = " WHERE pul_dead = FALSE AND pul_client = " . $userLogged->getClient();

$pul_user = Input::int("pul_user");
if ($pul_user != NULL) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= " pul_user_requested = $pul_user ";
}

$number = Input::int("pul_number");
if ($number > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "pul_number = $number";
}

$pul_status = Input::int("pul_status");
if ($pul_status != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "pul_status = $pul_status";
}

$dateFrom = Input::str("pul_creation_from");
$dateTo = Input::str("pul_creation_to");

if (! empty ( $dateFrom )) {
	if ($dateFrom = DateTime::createFromFormat ( "Y-m-d", $dateFrom )) {
		if (! empty ( $dateTo )) {
			if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= " DATE_FORMAT(pul_dt_creation, '%Y-%m-%d') BETWEEN '" . $dateFrom->format ( 'Y-m-d' ) . "' AND '" . $dateTo->format ( 'Y-m-d' ) . "'";
			}
		} else {
			if ($sWhere == "") {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			$sWhere .= " DATE_FORMAT(pul_dt_creation, '%Y-%m-%d') = '" . $dateFrom->format ( 'Y-m-d' ) . "' ";
		}
	}
} else if (! empty ( $dateTo )) {
	if ($dateTo = DateTime::createFromFormat ( "Y-m-d", $dateTo )) {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " DATE_FORMAT(pul_dt_creation, '%Y-%m-%d') <= '" . $dateTo->format ( 'Y-m-d' ) . "' ";
	}
}
// Data de retirada
$dateWithdrawalFrom = Input::str("pul_withdrawal_from");
$dateWithdrawalTo = Input::str("pul_withdrawal_to");

if (! empty ( $dateWithdrawalFrom )) {
	if ($dateWithdrawalFrom = DateTime::createFromFormat ( "Y-m-d", $dateWithdrawalFrom )) {
		if (! empty ( $dateWithdrawalTo )) {
			if ($dateWithdrawalTo = DateTime::createFromFormat ( "Y-m-d", $dateWithdrawalTo )) {
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= " DATE_FORMAT(pul_dt_withdrawal, '%Y-%m-%d') BETWEEN '" . $dateWithdrawalFrom->format ( 'Y-m-d' ) . "' AND '" . $dateWithdrawalTo->format ( 'Y-m-d' ) . "'";
			}
		} else {
			if ($sWhere == "") {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			$sWhere .= " DATE_FORMAT(pul_dt_withdrawal, '%Y-%m-%d') = '" . $dateWithdrawalFrom->format ( 'Y-m-d' ) . "' ";
		}
	}
} else if (! empty ( $dateWithdrawalTo )) {
	if ($dateWithdrawalTo = DateTime::createFromFormat ( "Y-m-d", $dateWithdrawalTo )) {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= " DATE_FORMAT(pul_dt_withdrawal, '%Y-%m-%d') <= '" . $dateWithdrawalTo->format ( 'Y-m-d' ) . "' ";
	}
}

/* Individual column filtering */
/*for($i = 0; $i < count ( $aColumns ); $i ++) {
	if (isset ( $_POST ['bSearchable_' . $i] ) && $_POST ['bSearchable_' . $i] == "true" && $_POST ['sSearch_' . $i] != '') {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns [$i] . " LIKE '%" . mysql_real_escape_string ( $_POST ['sSearch_' . $i] ) . "%' ";
	}
}*/

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace ( " , ", " ", implode ( ", ", $aColumns ) ) . " FROM $sTable 
		LEFT JOIN users on usr_id = pul_user_requested 
        $sWhere
        $sOrder
        $sLimit";

$rResult = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error_: ' . mysqli_error ( $db->con ) );

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_error ( $db->con ) );
$aResultFilterTotal = mysqli_fetch_array ( $rResultFilterTotal );
$iFilteredTotal = $aResultFilterTotal [0];

/* Total data set length */
$sWhere = " WHERE pul_dead = FALSE AND pul_client = " . $userLogged->getClient();

$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable $sWhere";
$rResultTotal = $db->query ( $sQuery ) or fatal_error ( 'MySQL Error: ' . mysqli_error ( $db->con ) );
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
		if (strcasecmp ( "pul_dt_creation", $aColumns [$i] ) == 0) {
			$date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow ["pul_dt_creation"] );
			$row [] = $date->format ( "d/m/Y H:i" );
		} else if (strcasecmp ( "pul_dt_withdrawal", $aColumns [$i] ) == 0) {
			if(!empty($aRow ["pul_dt_withdrawal"])) {
				$date = DateTime::createFromFormat ( "Y-m-d H:i:s", $aRow ["pul_dt_withdrawal"] );
				$row [] = $date->format ( "d/m/Y H:i" );
			} else {
				$row [] = "";
			}
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