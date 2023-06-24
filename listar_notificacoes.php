<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/model/Notification.php");
include_once (dirname(__FILE__) . "/core/control/UserController.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");

use DocBox\model\NotificationType;
use Docbox\control\ClientController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use DocBox\model\NotificationEvent;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$usrController = new UserController($db);
$cliController = new ClientController($db);
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
                        <h3 class="text-themecolor mb-0 mt-0">Notificações</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Início</a></li>
                            <li class="breadcrumb-item active">Listar Notificações</li>
                        </ol>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Filtros</h4>
                                <h6 class="card-subtitle">Utilize os filtros abaixo para pesquisar os usuários.</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Tipo</label>
		                                            <select id='txt_type' name='txt_type' class="form-control">
		                                                <option value="" selected>TODOS</option>
                                                        <option value="<?= NotificationType::REQUEST ?>">Pedido</option>
    		                                            <option value="<?= NotificationType::DEVOLUTION ?>">Devolução</option>
    		                                            <option value="<?= NotificationType::WITHDRAWAL ?>">Retirada</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="txt_date_from">Data inicial</label>
		                                        <input id='txt_date_from' name='txt_date_from' type="date" class="form-control" value="<?php echo isset($date_from) ? $date_from : ""; ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="txt_date_to">Data final</label>
		                                        <input id='txt_date_to' name='txt_date_to' type="date" class="form-control" value='<?php echo isset($date_to) ? $date_to : ""; ?>'>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Cliente</label>
		                                            <select id='txt_client' name='txt_client' class="form-control">
		                                                <option value="" selected>Selecione o cliente</option>
                                                        <?php
	                                            			$clients = $cliController->getClients();
	                                            			foreach($clients as $client) {
	                                            				echo "<option value='" . $client->getId() . "'>" . $client->getName() . "</option>";
	                                            			}
			                                            ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="txt_user">Nome do usuário</label>
			                                        <input id='txt_user' name='txt_user' type="text" class="form-control" placeholder="" autocomplete="off">
		                                    	</div>
		                                    </div>
                                		</div>
                                	</div>
                                	<button type="reset" class="btn btn-default">Limpar</button>
									<button type="submit" class="btn btn-primary">Filtrar</button>
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
                                <h6 class="card-subtitle">Esta tabela exibe todos os documentos cadastrados gerenciados pelo Web Software conforme os filtros indicados. ;)</h6>
                                <div class="table-responsive m-t-40">
                                    <table id="data-table-basic" class="table table-bordered table-striped">
                                    	<thead>
		                                <tr>
		                                    <th>#</th>
		                                    <th>#</th>
		                                    <th>Cliente</th>
		                                    <th>Usuário</th>
		                                    <th>Data/Hora</th>
		                                    <th>Mensagem</th>
		                                    <th>Evento</th>
		                                </tr>
		                                </thead>
		                                <tfoot>
		                                <tr>
		                                    <th>#</th>
		                                    <th>#</th>
		                                    <th>Cliente</th>
		                                    <th>Usuário</th>
		                                    <th>Data/Hora</th>
		                                    <th>Mensagem</th>
		                                    <th>Evento</th>
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
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(document).ready(function() {
        	var tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [4, "desc"],
            	"language":{
            	    "sEmptyTable": "Nenhum registro encontrado",
            	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            	    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            	    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            	    "sInfoPostFix": "",
            	    "sInfoThousands": ".",
            	    "sLengthMenu": "_MENU_ resultados por página",
            	    "sLoadingRecords": "Carregando...",
            	    "sProcessing": "Processando...",
            	    "sZeroRecords": "Nenhum registro encontrado",
            	    "sSearch": "Pesquisar",
            	    "oPaginate": {
            	        "sNext": "Próximo",
            	        "sPrevious": "Anterior",
            	        "sFirst": "Primeiro",
            	        "sLast": "Último"
            	    }, "oAria": {
            	        "sSortAscending": ": Ordenar colunas de forma ascendente",
            	        "sSortDescending": ": Ordenar colunas de forma descendente"
            	    }
            	},
            	"sAjaxSource": "./core/data-tables/TableNotifications.php",
            	"sServerMethod" : "POST",
            	"fnServerParams" : function(aoData) {
    				aoData.push(
    					{"name": "txt_user", "value" : $("#txt_user").val()},
    					{"name": "txt_type", "value" : $("#txt_type").val()},
    					{"name": "txt_client", "value" : $("#txt_client").val()},
    					{"name": "txt_date_from", "value" : $("#txt_date_from").val()},
        				{"name": "txt_date_to", "value" : $("#txt_date_to").val()},
    					{"name": "userprofile", "value" : $("#userprofile").val()});
            	},
            	"aoColumnDefs" : [{
                    	"aTargets": [0, 6],
                    	"visible":false
                	}, {
                		"aTargets": [1],
                		orderable: false,
                        width: 72,
                        "mRender": function(d,t,f) {
                            var obId = f[5];
                            if(obId == <?= NotificationType::REQUEST ?>) {
	                            return "<a href='visualizar_pedido.php?r=" + f[1] + "' class='btn btn-circle btn-primary'><i class='fa fa-eye'></i></a>";
                            } else if(obId == <?= NotificationType::DEVOLUTION ?>) {
                            	return "<a href='visualizar_devolucao.php?dev=" + f[1] + "' class='btn btn-circle btn-primary'><i class='fa fa-eye'></i></a>";
                            } else if(obId == <?= NotificationType::WITHDRAWAL ?>) {
                            	return "<a href='visualizar_retirada.php?r=" + f[1] + "' class='btn btn-circle btn-primary'><i class='fa fa-eye'></i></a>";
                            }
                            return "";
                        }
                    }, {
                        "aTargets":[5],
                        "mRender":function(d,t,f) {
                        	var type = f[5];
                        	var event = f[6];
                        	if(type == <?= NotificationType::REQUEST ?>) {
                            	if(event == <?= NotificationEvent::REGISTER?>) {
                                	return "Realizou um pedido"
                            	} else if (event == <?= NotificationEvent::CANCEL?>) {
                            		return "Cancelou um pedido"
                            	}
                        	} else if(type == <?= NotificationType::DEVOLUTION ?>) {
                            	if(event == <?= NotificationEvent::REGISTER?>) {
                            		return "Solicitou uma devolução"
                            	} else if (event == <?= NotificationEvent::CANCEL?>) {
                            		return "Cancelou uma devolução"
                            	}
                        	} else if(type == <?= NotificationType::WITHDRAWAL ?>) {
                            	if(event == <?= NotificationEvent::REGISTER?>) {
                            		return "Solicitou uma retirada"
                            	} else if (event == <?= NotificationEvent::CANCEL?>) {
                            		return "Cancelou uma retirada"
                            	}
                        	}
                            return "";
                         }
                    }]
            });
        	$("#form-filter-document").submit(function(e) {
            	tbDocuments.ajax.reload();
                e.preventDefault();
                $('html, body').animate({
            		scrollTop: $('#data-table-basic').offset().top - 128
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
