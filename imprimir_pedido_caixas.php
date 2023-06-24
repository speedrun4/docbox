<?php
include_once (dirname(__FILE__) . '/core/utils/Utils.php');
include_once (dirname(__FILE__) . '/core/model/DbConnection.php');
include_once (dirname(__FILE__) . '/core/control/UserSession.php');
include_once (dirname(__FILE__) . '/core/control/UserController.php');
include_once (dirname(__FILE__) . '/core/control/ClientController.php');
include_once (dirname(__FILE__) . '/core/control/RequestController.php');
include_once (dirname(__FILE__) . '/core/control/RequestStatusController.php');

use Docbox\control\ClientController;
use Docbox\control\RequestController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestStatus;
use function Docbox\utils\getReqParam;


$user = getUserLogged();

if($user == NULL) {
	exit();
}

$req_id = getReqParam("r", "int", "get");

$db = new DbConnection();
$reqController = new RequestController($db);
$staController = new RequestStatusController($db);
$cliController = new ClientController($db);
$userController = new UserController($db);

$request = $reqController->getRequest($req_id);
if($request == NULL) exit;
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
						<h2><?php echo $cliController->getClient($request->getClient())->getName(); ?></h2>
						<h4>Relatório de Pedido de Documentos</h4>
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
					<td class='data'><?php echo $request->getNumber(); ?></td>
					<td class='data'><?php echo $request->getDatetime()->format("d/m/Y H:i"); ?></td>
					<td class='data'>
						<?php echo $request->getUser()->getName(); ?>
						<br>
						<?php
						$departments = $userController->getUserDepartments($request->getUser()->getId());

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
					<td class='data'><?php echo $staController->getRequestStatusById($request->getStatus())->getName(); ?></td>
				</tr>
			</table>
			<br/>
			<table class="tbDocuments">
				<thead>
					<tr>
						<td colspan='2' align="center" style="border-bottom:1px solid gray;">Caixas</td>
					</tr>
					<tr>
    					<th>N&ordm;</th>
    					<th>Departamento</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$numBoxes = 0;
					$query = "SELECT * FROM caixas 
                        LEFT JOIN departamentos on dep_id = box_department 
                        WHERE box_request = " . $request->getId();

					if($result = $db->query($query)) {
					    while($row = $result->fetch_object()) {
					        $numBoxes++;
					    ?>
					    <tr>
					    	<td><?= $row->box_number; ?></td>
					    	<td><?= $row->dep_name; ?></td>
					    </tr>    
					    <?php
					    }
					}
					?>
				</tbody>
				<tfoot>
					<tr><th colspan="6">Pedido Nº <?php echo $request->getNumber(); ?></th></tr>
				</tfoot>
			</table><br/>
			<?php if($request->getStatus() != RequestStatus::RETURNED) { ?>
			<table class="tbDocuments">
				<thead>
					<tr>
						<td colspan="6" align="center" style="border-bottom:1px solid gray;">Documentos solicitados</td>
					</tr>
					<tr style="border-bottom:1px solid gray;">
						<th>Tipo</th>
						<th>Número</th>
						<th>Ano</th>
						<th>Letra</th>
						<th>Volume</th>
						<th>Caixa</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 0;
					$query = "SELECT dct_name, doc_number, doc_year, doc_letter, doc_volume, box_number from documentos
					LEFT JOIN tipos_documentos on doc_type = dct_id
					LEFT JOIN caixas ON box_id = doc_box 
                    INNER JOIN pedidos on box_request = req_id 
					LEFT JOIN status_pedidos ON req_status = sta_id
					WHERE box_request = " . $request->getId() . " and doc_dead = false";
					if($result = $db->query($query)) {
						while($row = $result->fetch_object()) {
							$i++;
					?>
					<tr class='<?php echo ($i % 2 == 0)?"even":"odd"; ?>'>
						<td class=''><?php echo utf8_encode($row->dct_name); ?></td>
						<td class='' style="text-align: right"><?php echo $row->doc_number > 0 ? $row->doc_number : ""; ?></td>
						<td class='' style="text-align: center;"><?php echo $row->doc_year; ?></td>
						<td class=''><?php echo strtoupper($row->doc_letter); ?></td>
						<td class='' style="text-align: center;"><?php echo $row->doc_volume > 0 ? $row->doc_volume : ""; ?></td>
						<td class='' style="text-align: right;"><?php echo $row->box_number; ?></td>
					</tr>
					<?php 
						}
					} ?>
				</tbody>
			</table><br/>
			<?php } ?>
			
			<table>
				<tr>
					<td colspan="2">Total de caixas do pedido: <?php echo $numBoxes; ?></td>
				</tr>
				<tr>
					<td colspan="2">Total de documentos do pedido: <?php echo $result->num_rows; ?></td>
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