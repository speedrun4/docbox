<?php
include_once (dirname(__FILE__) . '/core/utils/Input.php');
include_once (dirname(__FILE__) . '/core/model/DbConnection.php');
include_once (dirname(__FILE__) . '/core/control/UserSession.php');
include_once (dirname(__FILE__) . '/core/control/UserController.php');
include_once (dirname(__FILE__) . '/core/control/ClientController.php');
include_once (dirname(__FILE__) . '/core/control/WithdrawalController.php');

use Docbox\control\ClientController;
use Docbox\control\RequestController;
use Docbox\control\UserController;
use Docbox\control\WithdrawalController;

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\WithdrawalStatus;
use Docbox\utils\Input;

use function Docbox\utils\getReqParam;

$user = getUserLogged();

if($user == NULL) {
	exit();
}

$withdrawalID = Input::getInt("r");

$db = new DbConnection();
$withController = new WithdrawalController($db);
$cliController = new ClientController($db);
$userController = new UserController($db);

$withdrawal = $withController->getWithdrawalById($withdrawalID, $user->getClient());
if($withdrawal == NULL) exit;
?>
<!DOCTYPE html>
<html lang="pt">
	<head>
		<meta charset="utf-8">
		<title>DocBox</title>
		<!-- Favicon icon -->
    	<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
		<style type="text/css">
			body {
				/*background: rgb(204, 204, 204);*/
				font-family: Helvetica;
			}
			page {
				background: white;
				display: block;
				margin: 0 auto;
				margin-bottom: 0.5cm;
				padding-top: 0.27cm;
				page-break-after: always;
			}
			page[size="A4"] {
				width: 21cm;
/* 				height: 29.7cm; */
			}
			page[size="A4"][layout="portrait"] {
				width: 29.7cm;
				height: 21cm;
			}
			@media print {
				body, page {
					margin: 0;
					box-shadow: 0;
				}
			}
			table {
				width: 100%;
			}
			label {
				color: gray;
				width: 100%;
			}
			.header {
				border-top:1px solid black;
				border-bottom:1px solid black;
			}
			.header td:last-child {
				padding-right: 0.5cm;
			}
			.label {
				color: #777;
				font-size: 0.8rem;
				padding-top: 8px;
			}
			.data {
				border: 1px solid lightgray;
				border-radius: 8px;
				padding: 4px 4px;
				text-align: center;
			}
			.tbDocuments {
				border-collapse: collapse;
			}
			.tbDocuments tbody td {
				padding-left: 8px;
			}
			.tbDocuments tbody tr:nth-child(odd) {
				background-color: #cacaca;
			}
			.tbDocuments tbody td, .tbDocuments thead tr:nth-child(2) th {
				border-right: 1px solid gray;
			}
			.tbDocuments tbody td:nth-child(1), .tbDocuments thead th:nth-child(1){
				border-left: 1px solid gray;
			}
			.tbDocuments tbody tr:last-child td {
				border-bottom: 1px solid gray;
			}
			.departmentBlock {
				color: #555;
				font-size: 0.9rem;
			}
		</style>
	</head>
	<body>
		<page size="A4">
			<table style="border-collapse: collapse;">
				<tr class="header">
					<td style="width: 15%"><img alt="" src="assets/images/cliente<?php echo $user->getClient();?>.png" width="88"></td>
					<td align="center" colspan='2'>
						<h2><?php echo $cliController->getClient($withdrawal->getClient())->getName(); ?></h2>
						<h3>Relatório de Retirada de Documentos</h3>
					</td>
					<td align="right" style="width: 15%">
						<img alt="" src="assets/images/docbox_logo.png" width="64">
						<div><?php echo date("d/m/Y"); ?></div>
					</td>
				</tr>
			</table>
			<br>
			<table>
				<tr>
					<td colspan="4" align="center" style="border-bottom:1px solid gray;">Informações do pedido</td>
				</tr>
				<tr>
					<td class='label'>Nº</td>
					<td class='label'>Data/Hora</td>
					<td class='label'>Solicitante</td>
					<td class='label'>Situação do pedido</td>
				</tr>
				<tr>
					<td class='data'><?php echo $withdrawal->getNumber(); ?></td>
					<td class='data'><?php echo $withdrawal->getCreationDate()->format("d/m/Y H:i"); ?></td>
					<td class='data'>
						<?php echo $withdrawal->getUserRequested()->getName(); ?>
						<br>
						<?php
						$departments = $userController->getUserDepartments($withdrawal->getUserRequested()->getId());

						if(!empty($departments) && count($departments) > 0) {
							echo "<div class='departmentBlock'>";
							echo "(";
							for ($j = 0; $j < count($departments); $j++) {
								echo utf8_encode($departments[$j]->getName());
								if($j + 1 < count($departments)) echo ", ";
							}
							echo ")";
							echo "</div>";
						}
						?>
					</td>
					<td class='data'><?php
					if($withdrawal->getStatus() == WithdrawalStatus::OPEN) {
						echo "EM ABERTO";
					} else if($withdrawal->getStatus() == WithdrawalStatus::CANCELLED) {
						echo "CANCELADO";
					} else if($withdrawal->getStatus() == WithdrawalStatus::FINISHED) {
						echo "FINALIZADO";
					}
					?></td>
				</tr>
			</table>
			<br/>
			<table class="tbDocuments">
				<thead>
					<tr>
						<td colspan='2' align="center" style="border-bottom:1px solid gray;">Documentos</td>
					</tr>
					<tr>
    					<th>N&ordm;</th>
    					<th>Ano</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$numBoxes = 0;
					$docs = $withController->getWithdrawalDocs($withdrawal);
					foreach($docs as $doc) { ?>
					<tr>
						<td style='text-align:center'><?= $doc->getNumber() ?></td>
						<td style='text-align:center'><?= $doc->getYear() ?></td>
					</tr>    
					<?php } ?>
				</tbody>
			</table><br/>

			<table>
				<tr>
					<td colspan="2">Total de documentos do pedido: <?php echo count($docs); ?></td>
				</tr>
				<tr><td colspan="2" style="padding-top: 1cm">Atesto que recebi o(s) documento(s) acima mencionado(s)</td></tr>
				<tr><td colspan="2" style="padding-top: 0.5cm">Em: _____/_____/_____</td></tr>
				<tr>
					<td style="text-align:center; padding-top: 2cm">___________________________________</td>
					<td style="text-align:center; padding-top: 2cm">___________________________________</td>
				</tr>
				<tr>
					<td style="text-align:center;">Responsável pelo recebimento</td>
					<td style="text-align:center;">Responsável pela entrega</td>
				</tr>
			</table>
		</page>
	</body>
	<script type="text/javascript">
	<?php if(!file_exists(dirname(__FILE__) . "/DEVMACHINE.inc")) { ?>
		window.print();
	<?php } ?>
	</script>
</html>