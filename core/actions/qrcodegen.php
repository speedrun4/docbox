<?php
namespace Docbox\actions;

include_once (dirname(__FILE__) . "/../utils/Utils.php");
include_once (dirname(__FILE__) . "/../control/UserSession.php");
include_once (dirname(__FILE__) . "/../control/BoxController.php");
include_once (dirname(__FILE__) . "/../phpqrcode/qrlib.php");
include_once (dirname(__FILE__) . "/../model/User.php");

use Docbox\model\User;
use function Docbox\utils\getReqParam;
use function Docbox\control\getUserLogged;

$server__address = $_SERVER['SERVER_NAME'];

$user = getUserLogged();
if($user == NULL || $user->getProfile() != User::USER_ADMIN) {
    exit();
}

$box_num = getReqParam("n", "int", "get");
$cli_id = getReqParam("c", "int", "get");
// we need to be sure ours script does not output anything!!!
// otherwise it will break up PNG binary!
ob_start("callback");

// here DB request or some processing
$codeText = "https://$server__address/qr2box.php?n=$box_num&c=$cli_id";

// end of processing here
$debugLog = ob_get_contents();
ob_end_clean();

// outputs image directly into browser, as PNG stream
\QRcode::png($codeText, false, QR_ECLEVEL_L, 4);
// ($text, $outfile=false, $level=QR_ECLEVEL_L, $size=3, $margin=4, $saveandprint=false) 