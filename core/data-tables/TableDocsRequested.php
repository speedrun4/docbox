<?php
namespace Docbox\tables;

include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/UserController.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../model/RequestStatus.php");
include_once (dirname(__FILE__) . "/../model/RequestType.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../utils/Input.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use Docbox\model\RequestType;
use Docbox\utils\Input;
use function Docbox\utils\getReqParam;
use DateTime;
/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array (
		'dcr_id',
    'doc_box',
    'box_number',
    'doc_book',
    'doc_type',
    'doc_number',
    'doc_num_from',
    'doc_num_to',
    'doc_year',
    'doc_letter',
    'doc_volume',
    'doc_company',
    'doc_date',

    'box_request',  // 13
    'dcr_request',  // 14
    'box_blocked',  // 15
    'doc_file',     // 16
    'dep_name',     // 17
    'usr_name',     // 18
    'ret_id'		// 19
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "dcr_id";

/* DB table to use */
$sTable = "documentos_pedidos";

/* Database connection information */

$userLogged = getUserLogged();
if ($userLogged == NULL)
    exit();

$client = $userLogged->getClient();
if ($client == 0)
    exit();

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

$sWhere = " WHERE doc_dead=FALSE
		AND doc_client = $client
		AND (req_status = " . RequestStatus::ATTENDEND . "
		OR req_status = " . RequestStatus::RETURNING. ")
		AND req_type = " . RequestType::DOCUMENT . " ";

/**
 * FILTRO POR DEPARTAMENTO
 */
$sLeftJoinUsers = "";
if (! $userLogged->isAdmin()) {
    // $usrController = new UserController($db);
    // $departments = $usrController->getUserDepartmentIDs($userLogged->getId());

    /*$sLeftJoinUsers =
    " LEFT JOIN usuario_departamentos ON usd_department = box_department
      LEFT JOIN users ON usd_user = usr_id ";
*/
    // $sWhere .= " AND usd_user = " . $userLogged->getId();
    $sWhere .= " AND box_department in (SELECT usd_department FROM usuario_departamentos WHERE usd_user = " . $userLogged->getId() . ")";
}

$department = Input::int('doc_department');
if ($department > 0) {
	if ($sWhere == "") {
		$sWhere = "WHERE ";
	} else {
		$sWhere .= " AND ";
	}
	$sWhere .= " box_department = $department";
}

if (isset($_POST['doc_box']) && intval($_POST['doc_box']) != 0) {
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "doc_box=" . intval($_POST['doc_box']);
}

$box_marked = getReqParam("doc_marked", "int", "post");
$marks = isset($_POST['box_marks']) ? $_POST["box_marks"] : array();
if ($box_marked == 1) { // Selecionados
    if (is_array($marks) && count($marks) > 0) {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= "doc_box IN (" . implode(',', $marks) . ") ";
    }
} else if ($box_marked == 2) { // Não selecionados
    if (is_array($marks) && count($marks) > 0) {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= "doc_box NOT IN (" . implode(',', $marks) . ") ";
    }
}

$doc_marks = isset($_POST['doc_marks']) ? $_POST["doc_marks"] : array();
if ($box_marked == 1) { // Selecionados
    if (is_array($doc_marks) && count($doc_marks) > 0) {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= "$sIndexColumn IN (" . implode(',', $doc_marks) . ") ";
    }
} else if ($box_marked == 2) { // Não selecionados
    if (is_array($doc_marks) && count($doc_marks) > 0) {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $sWhere .= "$sIndexColumn NOT IN (" . implode(',', $doc_marks) . ") ";
    }
}

$format = Input::int('doc_format');
if($format == 19) {// Documento
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "doc_book = FALSE ";
} else if($format == 89) {// Livro
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "doc_book = TRUE ";
}

$box_number = Input::int('box_number');
if ($box_number != 0) {
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "box_number = $box_number";
}

$doc_volume = getReqParam('doc_volume', 'int', 'post');
if ($doc_volume != 0) {
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "doc_volume=$doc_volume";
}

$doc_number = Input::int("doc_number");
if ($doc_number > 0) {
    $sWhere .= " AND (doc_number = $doc_number OR $doc_number BETWEEN doc_num_from AND doc_num_to)";
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

$title = getReqParam('doc_title', 'str', 'post');
if (! empty($title)) {
    if ($sWhere == "") {
        $sWhere = "WHERE ";
    } else {
        $sWhere .= " AND ";
    }
    $sWhere .= "doc_company LIKE '%" . $title . "%' ";
}

$dateFrom = getReqParam("doc_date_from", "str", "post");
$dateTo = getReqParam("doc_date_to", "str", "post");

if (! empty($dateFrom)) {
    if ($dateFrom = DateTime::createFromFormat("Y-m-d", $dateFrom)) {
        if (! empty($dateTo)) {
            if ($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
                $sWhere .= " AND doc_date BETWEEN '" . $dateFrom->format('Y-m-d') . "' AND '" . $dateTo->format('Y-m-d') . "'";
            }
        } else {
            $sWhere .= " AND doc_date = '" . $dateFrom->format('Y-m-d') . "' ";
        }
    }
} else if (! empty($dateTo)) {
    if ($dateTo = DateTime::createFromFormat("Y-m-d", $dateTo)) {
        $sWhere .= " AND doc_date <= '" . $dateTo->format('Y-m-d') . "' ";
    }
}

$location = Input::int('doc_location');
if ($location > 0) {
    if ($location == 1) { // Em estoque
        $sWhere .= " AND ((box_request IS NULL OR box_request = 0) AND (doc_request IS NULL OR doc_request = 0)) ";
    } else if ($location == 2) { // Em pedido
        $sWhere .= " AND (box_request > 0 OR doc_request > 0) ";
    }
}

$doc_in_req = Input::int('doc_in_req');
if($doc_in_req == 1) {
	$sWhere .= " AND doc_request > 0 ";
}

$has_file = Input::boolean('doc_has_file');
if($has_file) {
    $sWhere .= " AND doc_file IS NOT NULL ";
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS dct_name, " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable
	LEFT JOIN documentos_devolucoes ON dre_doc_requested = dcr_id
	LEFT JOIN devolucoes ON dre_return = ret_id
	INNER JOIN documentos ON doc_id = dcr_document
    INNER JOIN pedidos ON dcr_request = req_id
    LEFT JOIN tipos_documentos on doc_type = dct_id
    LEFT JOIN caixas ON doc_box = box_id
    LEFT JOIN departamentos ON dep_id = box_department
    LEFT JOIN users on usr_id = req_user
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable INNER JOIN documentos ON doc_id = dcr_document WHERE doc_dead=FALSE AND doc_client = $client";
$rResultTotal = $db->query($sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($db->con));
$aResultTotal = mysqli_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

/*
 * Output
 */
$output = array (
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
