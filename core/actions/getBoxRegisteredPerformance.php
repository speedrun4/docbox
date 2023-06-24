<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/StatisticsController.php");

use Docbox\control\StatisticsController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use stdClass;

$user = getUserLogged();
if ($user == NULL) {
    exit();
}

$response = new stdClass();
$response->ok = false;
$response->error = "Não foi possível realizar a operação";
$response->type = "error";
$method = "post";

$date_from = trim(getReqParam("chart_from", "str", "$method"));
$date_to = trim(getReqParam("chart_to", "str", "$method"));

$db = new DbConnection();
$controller = new StatisticsController($db);

echo json_encode($controller->getBoxRegisteredPerformance($date_from, $date_to));