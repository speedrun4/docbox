<?php
include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/DocumentController.php");

use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;
use Docbox\control\DocumentController;

$title = getReqParam("title", "str", "get");

$db = new DbConnection();
$docController = new DocumentController($db);

$result = $docController->findTitles($title);

echo json_encode($result);