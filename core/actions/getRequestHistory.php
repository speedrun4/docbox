<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../model/DbConnection.php");
include_once (dirname(__FILE__) . "/../model/Request.php");
include_once (dirname(__FILE__) . "/../model/Devolution.php");
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/RequestController.php");

use Docbox\control\RequestController;
use Docbox\model\DbConnection;
use Docbox\model\Devolution;
use Docbox\model\Modification;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;
// Permite que o script seja acessado de outro servidor
// TODO Remover antes de enviar pra produção
// header("Access-Control-Allow-Origin: *");
/*
 * Array of database columns which should be read and sent back to DataTables. Use a space where
 * you want to insert a non-database field (for example a counter or static image)
 */
$aColumns = array(
    'mod_id',
    'usr_name',
    'mod_action',
    'mod_when'
);

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "mod_id";

/* DB table to use */
$sTable = "modificacoes";

/* Database connection information */
/*
$userLogged = getUserLogged();
if ($userLogged == NULL)
    exit();
*/
$db = new DbConnection();

$req_id = getReqParam("r", "int", "get");
$reqController = new RequestController($db);

$request = $reqController->getRequest($req_id);

if ($request == NULL /*|| $request->getClient() != $userLogged->getClient()*/) {
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . ", req_receipt_file_1, req_receipt_file_2 FROM $sTable
		INNER JOIN users on usr_id = mod_user
        INNER JOIN pedidos on req_id = mod_tb_id
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
    "CANCELLED" => false,
    "aaData" => array()
);

$actions = Modification::listRequestModificationActions();
$steps = array();
$files = array();

while ($aRow = mysqli_fetch_array($rResult)) {
    $steps[] = $aRow['mod_action'];

    if (strcasecmp($aRow['mod_action'], "U2" . RequestStatus::ATTENDEND) == 0) {
        if (! empty($aRow['req_receipt_file_1'])) {
            $files[]= $aRow['req_receipt_file_1'];
        }
    } else if (strcasecmp($aRow['mod_action'], "U2" . RequestStatus::COMPLETED) == 0) {
        if (! empty($aRow['req_receipt_file_2'])) {
            $files[]= $aRow['req_receipt_file_2'];
		}
	}
}

// Se for um pedido Cancelado
if (in_array ( Modification::UPDATE2CANCELED, $steps )) {
	$output ["CANCELLED"] = true;
}

if (!in_array ( Modification::UPDATE2RETURNED, $steps )) {
	if (($key = array_search(Modification::UPDATE2RETURNED, $actions)) !== false) {
		array_splice($actions, $key, 1);
	}
}

$percentage = 0;
if (in_array ( Modification::UPDATE2COMPLETED, $steps )) {
	$percentage = 100;
} else if (in_array ( Modification::UPDATE2RETURNING, $steps )) {// Se já foi feito devolução
	/** Devolução total retorna 100%, Se está devolvendo, calcula a porcentagem da devolução; 
	 * Pega número de documentos no pedido; 
	 * Pega número de documentos em devolução; 
	 * Calcula a percentagem */
	$query = "SELECT (
			(SELECT count(*) FROM devolucoes 
			INNER JOIN documentos_devolucoes ON dre_return = ret_id
			INNER JOIN documentos_pedidos ON dre_doc_requested = dcr_id 
			WHERE dcr_request = $req_id)
			/
			(SELECT count(*) FROM documentos_pedidos WHERE dcr_request = $req_id) * 100
        ) as total
		FROM devolucoes 
		INNER JOIN documentos_devolucoes ON dre_return = ret_id
		INNER JOIN documentos_pedidos ON dre_doc_requested = dcr_id 
		WHERE dcr_request = $req_id;";

	if($result = $db->query($query)) {
		if($row = $result->fetch_object()) {
			$percentage = $row->total;
		}
	}
}

$data = array();
for($i = 0; $i < count($actions); $i++) {
    if (strcasecmp($actions[$i], Modification::UPDATE2ATTENDEND) == 0 && count($files) > 0) {
        $data[] = array($actions[$i], in_array($actions[$i], $steps), $files[0]);
    } else if (strcasecmp($actions[$i], Modification::UPDATE2COMPLETED) == 0 && count($files) > 1) {
        $data[] = array($actions[$i], in_array($actions[$i], $steps), $files[1]);
    } else if(strcasecmp($actions[$i], Modification::UPDATE2RETURNING) == 0) {
    	$data[] = array($actions[$i], in_array($actions[$i], $steps), $percentage);
    } else if(strcasecmp($actions[$i], Modification::UPDATE2RETURNED) == 0) {
    	if (in_array ( Modification::UPDATE2RETURNED, $steps )) {
    		continue;
    	}
    } else {
        $data[] = array($actions[$i], in_array($actions[$i], $steps));
    }

	// Adiciona linha
    if(($i + 1) != count($actions)) {
        if(($i+1) < count($actions) && in_array($actions[$i+1], $steps)) {
            $data[] = array("L", true);
        } else {
            $data[] = array("L", false);
        }
    }
}

$output['aaData'] = $data;

echo json_encode($output);