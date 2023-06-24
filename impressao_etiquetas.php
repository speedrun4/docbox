<?php
/**
 * Imprimir etiquetas de caixas
 * 
 * -------------------------------
 * ## Configuração de impressão ##
 * -------------------------------
 * Margem: Sem margens
 * Layout: Retrato
 * Papel: Carta (Pimaco 6288)
 * Tamanho do Papel: 279,4mm x 215,9mm ou 8.5pol x 11pol
 */
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/model/User.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/BoxController.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/phpqrcode/qrlib.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");

use Docbox\control\ClientController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use function Docbox\utils\getReqParam;

$user = getUserLogged();
if($user == NULL) {
	header ( "Location: login.php");
	die();// and go to hell!
}

if(isset($_POST['lab_text'])) {
	$text = getReqParam ("lab_text", "str", "post");
	$position = getReqParam("lab_position", "int", "post");

	if(empty($text) || $position <= 0 || $position > 4) {
	    header("Location: login.php");
	}

	$db = new DbConnection ();
	$cliController = new ClientController ( $db );
	$client = $cliController->getClient ( $user->getClient () );
	
	if ($client == NULL) {
	    header ( "Location: login.php" );
	}
} else if(isset($_POST['lab_num_pages'])) { // Impressão de páginas
	$firstNumber = getReqParam ( "lab_number", "int", "post");
	$numPages = getReqParam ( "lab_num_pages", "int", "post");

	if ($numPages <= 0 || (empty($text) && $firstNumber <= 0) || $user->getProfile() == User::USER_COMMON) {
		header("Location: login.php");
	}

	$db = new DbConnection ();
	$boxController = new BoxController ( $db );
	$cliController = new ClientController ( $db );

	$client = $cliController->getClient ( $user->getClient () );

	if ($client == NULL) {
		header ( "Location: login.php" );
	}
} else if(isset($_POST['lab_box'])) {// Impressão de caixa específica
	$box_id = getReqParam("lab_box", "int", "post");
	$position = getReqParam("lab_position", "int", "post");

	$db = new DbConnection ();
	$boxController = new BoxController($db);
	$cliController = new ClientController ( $db );

	$client = $cliController->getClient ( $user->getClient () );
	$box = $boxController->getBoxById($box_id);

	if ($box == NULL || $box->getClient () != $user->getClient () || $position <= 0) {
		header ( "Location: login.php" );
		die ();
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>DocBox</title>
	<!-- Favicon icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
<style type="text/css">
@font-face {
	font-family: roboto;
	src: url(./fonts/roboto/Roboto-Regular-webfont.eot);
	src: url(./fonts/roboto/Roboto-Regular-webfont.eot?#iefix)
		format('embedded-opentype'),
		url(./fonts/roboto/Roboto-Regular-webfont.woff) format('woff'),
		url(./fonts/roboto/Roboto-Regular-webfont.ttf) format('truetype'),
		url(./fonts/roboto/Roboto-Regular-webfont.svg#icon) format('svg');
	font-weight: 400;
	font-style: normal
}
p {
	font-size: 36px;
	line-height: 50px;
	text-transform: none;
}
.birthdayPerson {
	font-size: 3em;
	text-transform: capitalize;
	/*line-height: 3.5em;*/
}
@media screen {
    body {
    	background: rgb(204, 204, 204);
    }
}
page {
	background: white;
	display: block;
	margin: 0 auto;
	/*margin-bottom: 0.5cm;*/
	margin-top: 0.27cm;
	text-transform: capitalize;
	/*page-break-after: always;*/
}
page[size="Carta"] {
	width: 21.59cm;
	height: 27.94cm;
}
page[size="Carta"][layout="portrait"] {
	height: 21.59cm;
	width: 27.94cm;
}
page div.label {
	width: 10.63cm;
	height: 13.70cm;
	display: inline-block;
	text-align: center;
    /* border: 1px inset blue;*/
	position: relative;
/* 	background: rgba(0, 100, 0, .2); */
}
.label img.label__city_img {
	margin-top: 1cm;
	height: 5cm;
}
.label img.label__qrcode_img {
    width: 3.4cm;
    height: 3.4cm;
}
.label h1 {
	font-size: 0.8cm;
	font-family: roboto;
/*     background: rgba(0, 100, 150, 0.3); */
    margin: 0.3cm 0 0 0;
}
.label h2 {
	font-size: 1.5cm;
	margin: 0;
/* 	background : rgba(255, 0, 0, .3); */
}
@media print {
	body, page {
		margin: 0;
		box-shadow: 0;
	}
}
</style>
</head>
<body>
	<?php 
	if(isset($_POST['lab_num_pages'])) {
		for($i = 0; $i < $numPages; $i++) {?>
		<page size="Carta">
			<?php for($j = 0; $j < 4; $j++) { ?>
			<div class="label">
				<img class="label__city_img" src="img/cliente<?php echo $user->getClient(); ?>.png">
				<h1><?= $client->getLabelText() ?></h1>
				<?php if(!empty($text)) { ?>
					<h3><?php echo ($text); ?></h3>
				<?php } else { ?>
					<h2><?php echo $firstNumber + ($i * 4) + $j; ?></h2>
					<img class="label__qrcode_img" src="core/actions/qrcodegen.php?c=<?= $client->getId() ?>&n=<?= $firstNumber + ($i * 4) + $j ?>" alt="QR-Code">
				<?php } ?>
			</div>
			<?php } ?>
		</page>
	<?php }
	} else { ?>
		<page size="Carta">
			<?php for($j = 0; $j < 4; $j++) {// Percorre para imprimir na posição correta
				if(($j + 1) == $position) {
			?>
			<div class="label">
				<img class="label__city_img" src="img/cliente<?php echo $user->getClient(); ?>.png">
				<h1><?= $client->getLabelText() ?></h1>
				<?php if(!empty($text)) { ?>
					<h3><?php echo strtoupper(($text)); ?></h3>
					<img class="label__qrcode_img" src="img/select.png" style="opacity: 0">
				<?php } else { ?>
					<h2><?php echo $box->getNumber(); ?></h2>
    				<img class="label__qrcode_img" src="core/actions/qrcodegen.php?c=<?= $box->getClient() ?>&n=<?= $box->getNumber() ?>" alt="QR-Code">
				<?php } ?>
			</div>
			<?php } else { ?>
			<div class="label">
				<img class="label__city_img" src="img/select.png" style="opacity: 0">
				<h1 style="color: white"><?= $client->getLabelText() ?></h1>
				<h2>&nbsp;</h2>
				<img class="label__qrcode_img" src="img/select.png" style="opacity: 0">
			</div>
			<?php
				}
			} ?>
		</page>
	<?php } ?>
	<script type="text/javascript">
		// window.print();
	</script>
</body>
</html>