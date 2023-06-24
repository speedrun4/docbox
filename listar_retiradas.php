<?php
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/utils/Input.php");
include_once (dirname(__FILE__) . "/core/model/Request.php");
include_once (dirname(__FILE__) . "/core/model/RequestType.php");
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/model/WithdrawalStatus.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/UserController.php");
include_once (dirname(__FILE__) . "/core/control/RequestController.php");
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");

use Docbox\control\DocumentTypeController;
use Docbox\control\RequestController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\RequestType;
use function Docbox\utils\getReqParam;
use Docbox\utils\Input;
use Docbox\model\WithdrawalStatus;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$usrController = new UserController($db);
$reqController = new RequestController($db);
$doctypeController = new DocumentTypeController($db);

$function = Input::getInt('f');

if($function == 777) {
	$yearReceived = getReqParam('year', 'int', 'get');

	// Exibe somente os pedidos do mês...
	$date_from = date('Y') . "-" . date('m') . "-01";
	$dateFromObj = DateTime::createFromFormat("Y-m-d", $date_from);
	$dateFromObj->add(new DateInterval("P1M"))->sub(new DateInterval("P1D"));
	$date_to = $dateFromObj->format("Y-m-d");
}

$req_status = $function != 33 && $function != 5 ? $function : 0;
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <?php include('head.php'); ?>
    <link href="assets/plugins/datatables/media/css/dataTables.foundation.css" rel="stylesheet">
</head>

<body class="fix-header card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
   		<?php
	        include('header.php');
	        include('aside_menu.php');
        ?>
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles">
                    <div class="col-md-6 col-8 align-self-center">
                        <h3 class="text-themecolor mb-0 mt-0">Retiradas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Retiradas</a></li>
                            <li class="breadcrumb-item active">Listar Retiradas</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Filtros</h4>
                                <h6 class="card-subtitle">Utilize os filtros abaixo para pesquisar nos pedidos.</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Status</label>
		                                            <select id='pul_status' name='pul_status' class="form-control">
		                                                <option value="">Selecione...</option>
		                                                <option value="1">EM ABERTO</option>
														<option value="2">FINALIZADO</option>
														<option value="3">CANCELADO</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="pul_number">N&ordm; da retirada</label>
			                                        <input id="pul_number" name='pul_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="pul_creation_from">Data criação de</label>
		                                        <input id='pul_creation_from' name='pul_creation_from' type="date" class="form-control" value="<?php echo isset($date_from) ? $date_from : ""; ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="pul_creation_to">Data criação até</label>
		                                        <input id='pul_creation_to' name='pul_creation_to' type="date" class="form-control" value='<?php echo isset($date_to) ? $date_to : ""; ?>'>
		                                    </div>
                                		</div>
                                    	<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Usuário</label>
		                                            <select id='pul_user' name='pul_user' class="form-control">
		                                                <option value="">Selecione...</option>
		                                                    <?php
		                                            			$users = $usrController->getUsers();
		                                            			foreach($users as $usr) {
		                                            				echo "<option value='" . $usr->getId() . "'>" . utf8_decode($usr->getName()) . "</option>";
		                                            			}
				                                            ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                        </div>
                                        <div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="pul_withdrawal_from">Data retirada de</label>
		                                        <input id='pul_withdrawal_from' name='pul_withdrawal_from' type="date" class="form-control" value="<?php echo isset($date_from) ? $date_from : ""; ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="pul_withdrawal_to">Data retirada até</label>
		                                        <input id='pul_withdrawal_to' name='pul_withdrawal_to' type="date" class="form-control" value='<?php echo isset($date_to) ? $date_to : ""; ?>'>
		                                    </div>
                                		</div>
                                	</div>
                                	<button type="reset" class="btn btn-default">Limpar</button>
									<button type="submit" class="btn btn-primary"><i class='fa fa-search'></i> Filtrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Resultados</h4>
                                <h6 class="card-subtitle">Esta tabela exibe todos os pedidos cadastrados gerenciados pelo Web Software conforme os filtros indicados.</h6>
                                <div class="table-responsive m-t-40">
                                    <table id="data-table-basic" class="table table-bordered table-striped">
                                    	<thead>
		                                <tr>
		                                    <th>Ações</th>
		                                    <th>N&ordm;</th>
		                                    <th>Criado por</th>
		                                    <th>Data criação</th>
		                                    <th>Data retirada</th>
		                                    <th>Status</th>
		                                </tr>
		                                </thead>
		                                <tfoot>
		                                <tr>
		                                    <th>Ações</th>
		                                    <th>N&ordm;</th>
		                                    <th>Criado por</th>
		                                    <th>Data criação</th>
		                                    <th>Data retirada</th>
		                                    <th>Status</th>
		                                </tr>
		                                </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Page Content -->
                <!-- ============================================================== -->
                <?php include('right_sidebar.php'); ?>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
             <?php include('footer.php'); ?>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.min.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(document).ready(function() {
        	var tbRequests = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [1, "desc"],
            	"language" : {
            	    "sEmptyTable": "Nenhuma retirada encontrada",
            	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ retiradas",
            	    "sInfoEmpty": "Mostrando 0 até 0 de 0 retiradas",
            	    "sInfoFiltered": "(Filtrados de _MAX_ retiradas)",
            	    "sInfoPostFix": "",
            	    "sInfoThousands": ".",
            	    "sLengthMenu": "_MENU_ resultados por página",
            	    "sLoadingRecords": "Carregando...",
            	    "sProcessing": "Processando...",
            	    "sZeroRecords": "Nenhuma retirada encontrada",
            	    "sSearch": "Pesquisar",
            	    "oPaginate": {
            	        "sNext": "Próximo",
            	        "sPrevious": "Anterior",
            	        "sFirst": "Primeiro",
            	        "sLast": "Último"
            	    },
            	    "oAria": {
            	        "sSortAscending": ": Ordenar colunas de forma ascendente",
            	        "sSortDescending": ": Ordenar colunas de forma descendente"
            	    }
            	},
            	"sAjaxSource": "./core/data-tables/TableWithdrawals.php",
            	"sServerMethod" : "POST",
            	"fnServerParams" : function(aoData) {
            		aoData.push(
        				{"name": "pul_number", "value" : $("#pul_number").val()},
        				{"name": "pul_creation_from", "value" : $("#pul_creation_from").val()},
        				{"name": "pul_creation_to", "value" : $("#pul_creation_to").val()},
        				{"name": "pul_user", "value" : $("#pul_user").val()},
                        {"name": "pul_withdrawal_from", "value" : $("#pul_withdrawal_from").val()},
        				{"name": "pul_withdrawal_to", "value" : $("#pul_withdrawal_to").val()},
        				{"name": 'pul_status', "value" : $("#pul_status").val()},
        				{"name": "req_open_dev", "value" : $("#req_open_dev").val()});
            	}, 
            	"aoColumnDefs" : [{
	            		"aTargets" : [0],
	            		orderable : false,
	                    width : 72,
	                    "mRender": function(d,t,f) {
	                    	return "<a href='visualizar_retirada.php?r=" + d + "' class='btn btn-circle btn-primary waves-effect'><i class='fa fa-eye'></i></a>";
                        }
                    }, {
                        "aTargets": [5],
                        width: 140,
                        className: 'dt-body-center',
                        "mRender": function(d, t, f) {
                            if (d == <?= WithdrawalStatus::OPEN?>) {
                                return "EM ABERTO";
                            } else if(d == <?= WithdrawalStatus::FINISHED?>) {
                            	return "FINALIZADO";
                            } else if(d == <?= WithdrawalStatus::CANCELLED ?>) {
								return "CANCELADO";
							}
                        }
                    }
                ]
            });
        	$("#form-filter-document").submit(function(e) {
            	tbRequests.ajax.reload();
                e.preventDefault();
                $('html, body').animate({
            		scrollTop: $('#data-table-basic').offset().top - 300
            	}, 1000);
            });
        });
    </script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>
