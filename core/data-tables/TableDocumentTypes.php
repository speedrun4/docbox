<?php
namespace Docbox\tables;

include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'dct_id',
    'dct_id',
    'dct_name',
	'dct_preffix'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "dct_id";

/* DB table to use */
$sTable = "tipos_documentos";

$userLogged = getUserLogged();
if ($userLogged == NULL) {
    exit();
}

/* Database connection information */
$db = new DbConnection();

/*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
 * no need to edit below this line
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

$sWhere = " WHERE dct_dead=FALSE and dct_client = " . $userLogged->getClient();
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable 
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
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM $sTable where dct_dead=false";
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
        if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = ($aRow[$aColumns[$i]]);
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);