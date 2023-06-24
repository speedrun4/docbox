<?php
namespace Docbox\tables;

include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/UserController.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../utils/Input.php");

use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\utils\Input;
use function Docbox\utils\getReqParam;
use DateTime;
/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
	'doc_id',
	'doc_box',
	'box_number',
	'doc_type',
	'doc_number',
	'doc_year',//5
	'doc_letter',
	'doc_volume',
	'doc_company',
	'doc_date',//9
	'box_request',//10
    'doc_request',//11
	'doc_file'//12
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "doc_id";

/* DB table to use */
$sTable = "documentos";

$client = 0;
$userLogged = getUserLogged();

if($userLogged) {
    $client = $userLogged->getClient();
} else {
    $client = Input::int('c');
}

if ($client == 0) die();

$db = new DbConnection();

/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there
 * is no need to edit below this line
 */

/*
 * Local functions
 */
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

$sWhere = " WHERE doc_dead=FALSE AND doc_book = FALSE AND doc_client = $client ";

/** FILTRO POR DEPARTAMENTO */
$sLeftJoinUsers = "";
if(isset($userLogged) && !$userLogged->isAdmin()) {
	$usrController = new UserController($db);
	$departments = $usrController->getUserDepartmentIDs($userLogged->getId());
	
	$sLeftJoinUsers = 
	" LEFT JOIN usuario_departamentos ON usd_department = box_department 
      LEFT JOIN users ON usd_user = usr_id ";
	
	$sWhere .= " AND usd_user = " . $userLogged->getId();
}

if (isset($_POST['doc_box']) && intval($_POST['doc_box']) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_box=" . intval($_POST['doc_box']);
}

$box_number = getReqParam('box_number', 'int', 'post');
if ($box_number != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "box_number=" . intval($_POST['box_number']);
}

$doc_number = getReqParam("doc_number", "int", "post");
if ($doc_number != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_number = $doc_number ";
}

if (isset($_POST['doc_year']) && intval($_POST['doc_year']) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_year=" . intval($_POST['doc_year']);
}

if (isset($_POST['doc_type']) && intval($_POST['doc_type']) != 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_type=" . intval($_POST['doc_type']);
}

$letter = getReqParam("doc_letter", "str", "post");

if (! empty($letter)) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_letter LIKE '$letter'";
}

if (isset($_POST['doc_title']) && ! empty($_POST['doc_title'])) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= "doc_company LIKE '%" . ($_POST['doc_title']) . "%' ";
}

$dateFrom = getReqParam("doc_date_from", "str", "post");
$dateTo = getReqParam("doc_date_to", "str", "post");

if(!empty($dateFrom)) {
	if($dateFrom = DateTime::createFromFormat("Y-m-d", $dateFrom)) {
		if(!empty($dateTo)) {
			if($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
				$sWhere .= " AND doc_date BETWEEN '" . $dateFrom->format('Y-m-d') . "' AND '" . $dateTo->format('Y-m-d') . "'";
			}
		} else {
			$sWhere .= " AND doc_date = '" . $dateFrom->format('Y-m-d') . "' ";
		}
	}
} else if(!empty($dateTo)) {
	if($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
		$sWhere .= " AND doc_date <= '" . $dateTo->format('Y-m-d') . "' ";
	}
}

/*
if (isset($_POST['doc_date_from']) && ! empty(trim($_POST['doc_date_from']))) {
	if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[\d]{4}$/", $_POST['doc_date_from'])) {
		$doc_date_from = DateTime::createFromFormat("d/m/Y", $_POST['doc_date_from']);
		$doc_date_from = $doc_date_from->format('Y-m-d');

		if (isset($_POST['doc_date_to']) && ! empty(trim($_POST['doc_date_to']))) {
			if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[\d]{4}$/", $_POST['doc_date_to'])) {
				$doc_date_to = DateTime::createFromFormat("d/m/Y", $_POST['doc_date_to']);
				$doc_date_to = $doc_date_to->format('Y-m-d');

				$sWhere .= " AND doc_date BETWEEN '$doc_date_from' AND '$doc_date_to'";
			}
		} else {
			$sWhere .= " AND doc_date = '$doc_date_from' ";
		}
	}
} else if (isset($_POST['doc_date_to']) && ! empty(trim($_POST['doc_date_to']))) {
	if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[\d]{4}$/", $_POST['doc_date_to'])) {
		$doc_date_to = DateTime::createFromFormat("d/m/Y", $_POST['doc_date_to']);
		$doc_date_to = $doc_date_to->format('Y-m-d');

		$sWhere .= " AND doc_date <= '$doc_date_to' ";
	}
}*/

$doc_location = getReqParam('doc_location', 'int', 'post');
if ($doc_location != 0) {
    if ($doc_location == 1) { // Em estoque
		$sWhere .= " AND ((box_request IS NULL OR box_request = 0) AND (doc_request IS NULL OR doc_request = 0)) ";
    } else if ($doc_location == 2) { // Em pedido
		$sWhere .= " AND (box_request > 0 OR doc_request > 0) ";
	}
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i ++) {
	if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_POST['sSearch_' . $i]) . "%' ";
	}
}

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS dct_name, " .
	str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable  
		LEFT JOIN tipos_documentos on doc_type = dct_id  
		LEFT JOIN caixas ON doc_box = box_id 
        LEFT JOIN pedidos ON box_request = req_id 
		$sLeftJoinUsers 
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable where doc_dead=false and doc_client = $client";
$rResultTotal = $db->query($sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($db->con));
$aResultTotal = mysqli_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

/*
 * Output
 */
$output = array(
	"sEcho" => isset($_POST['sEcho']) ? intval($_POST['sEcho']) : 0,
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

while ($aRow = mysqli_fetch_array($rResult)) {
	$row = array();
	for ($i = 0; $i < count($aColumns); $i ++) {
		if (strcasecmp("doc_type", $aColumns[$i]) == 0) {
			$row[] = $aRow["dct_name"];
		} else if (strcasecmp("doc_date", $aColumns[$i]) == 0) {
			$date = "";
			if ($aRow['doc_date'] != NULL) {
				if ($date = DateTime::createFromFormat("Y-m-d", $aRow['doc_date'])) {
					$date = $date->format("d/m/Y");
				}
			}
			$row[] = $date;
		} else if (strcasecmp("doc_letter", $aColumns[$i]) == 0) {
			$row[] = strtoupper($aRow[$aColumns[$i]]);
		} else if ($aColumns[$i] != ' ') {
		    if(file_exists(dirname(__FILE__) . "/../../DEVMACHINE.inc")) {
                $row[] = utf8_encode($aRow[$aColumns[$i]]);
		    } else {
	           $row[] = $aRow[$aColumns[$i]];
		    }
		}
	}
	$output['aaData'][] = $row;
}

echo json_encode($output);
?>