<?php
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/UserController.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");

use Docbox\control\ClientController;
use Docbox\control\DepartmentController;
use Docbox\control\UserController;

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$departController = new DepartmentController($db);
$cliController = new ClientController($db);
$usrController = new UserController($db);
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
        <svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /></svg>
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
                        <h3 class="text-themecolor mb-0 mt-0">Caixas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Caixas</a></li>
                            <li class="breadcrumb-item active">Listar caixas</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                        <a href='#modalRegisterBox' data-toggle='modal' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Novo</a>
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
                                <h6 class="card-subtitle">Utilize os campos abaixo para filtrar os resultados da tabela.</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="form_box_number">Nº da caixa</label>
			                                        <input id='form_box_number' name='form_box_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
                                			<div class="form-group">
                                                <label class="control-label">Departamento</label>
                                                <select id='form_box_department' name='form_box_department' class="form-control">
                                                	<option value="" selected>Selecione</option>
                                                    <?php
														$modalDepartments = $departController->getDepartments($user->getClient());
														$userDepartments = $user->getDepartments();

														foreach ($modalDepartments as $iDepartment) {
															if(in_array($iDepartment->getId(), $userDepartments) || $user->isAdmin()) {
																echo "<option value='" . $iDepartment->getId() . "'>&nbsp;&nbsp;&nbsp;" . $iDepartment->getName() . "</option>";
															}
														}
                                                        ?>
                                                </select>
                                            </div>
                                		</div>
                                		<?php if($user->isAdmin()) { ?>
                                    		<div class='col-md-3'>
                                    			 <div class="form-group">
                                    			 	<label class="control-label">Caixa selada</label>
                                                    <select id='form_box_sealed' name='form_box_sealed' class="form-control">
                                                    	<option value=''>Todas</option>
                                                    	<option value='1'>Sim</option>
                                                    	<option value='2'>Não</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Usuário</label>
		                                            <select id='box_user' name='box_user' class="form-control">
		                                                <option value="" selected>Selecione...</option>
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
                                    	<?php } ?>
                                	</div>
                                	<button type="reset" class="btn btn-default">Limpar</button>
									<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> &nbsp;Filtrar</button>
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
			                                    <th>Ações</th>
			                                    <th>Número</th>
			                                    <th>Departamento</th>
			                                    <th>Local</th>
			                                    <?php if($user->isAdmin()) { ?>
			                                    <th>Selada</th>
			                                    <th>Registro</th>
			                                    <?php } ?>
			                                </tr>
		                                </thead>
		                                <tfoot>
			                                <tr>
			                                    <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Número</th>
			                                    <th>Departamento</th>
			                                    <th>Local</th>
			                                    <?php if($user->isAdmin()) { ?>
			                                    <th>Selada</th>
			                                    <th>Registro</th>
			                                    <?php } ?>
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
    <?php include("modal_register_box.php"); ?>
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
    <!--Custom JavaScript -->
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
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
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
    	var tbDocuments = null;
        $(document).ready(function() {
        	tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [2, "asc"],
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
            	    },
            	    "oAria": {
            	        "sSortAscending": ": Ordenar colunas de forma ascendente",
            	        "sSortDescending": ": Ordenar colunas de forma descendente"
            	    }
            	},
            	"sAjaxSource": "./core/data-tables/DataTablesBoxes.php",
            	"sServerMethod": "POST",
            	"fnServerParams" : function(aoData) {
    				aoData.push (
    					{"name": "box_number", "value" : $("#form_box_number").val()},
    					{"name": "box_sealed", "value" : $("#form_box_sealed").val()},
    					{"name": "box_department", "value" : $("#form_box_department").val()},
                        {"name": "box_user", "value" : $("#box_user").val()}
    				);
            	},
            	"aoColumnDefs" : [{
                    	"aTargets": [0],
                    	"visible":false
                	}, {
                		"aTargets": [1],
                		orderable: false,
                        /*width: 80,*/
                        "mRender": function(d,t,f) {
                            var content = "<a data-toggle='tooltip' title='Visualizar caixa' href='visualizar_caixa.php?box=" + d + "' class='btn btn-circle btn-primary'><i class='fa fa-eye'></i></a>";
							<?php if($user->isAdmin()) { ?>
                            	content += "<a data-toggle='tooltip' title='Transferir documentos' href='transferir_documentos.php?box=" + d + "' class='btn btn-circle btn-success'><i class='fa fa-exchange-alt'></i></a>";
							<?php } ?>
                            return content;
                        }
                    }, {
                        "aTargets": [2],
                        width: "30%"
                    }, {
                        "aTargets": [3],
                        "visible": true,
                        width: "30%"
                    }, {
                    	"aTargets": [4],
                    	width:"30%",
                    	"mRender": function(d,t,f) {
                        	if(d > 0) {
                            	return "<a href='visualizar_pedido_caixas.php?r=" + d + "'>Em pedido</a>";
                            } else {
                                return '<?= COMPANY_NAME ?>';
                            }
                        }
                    }
                    <?php if($user->isAdmin()) { ?>
                    , {
                        'aTargets':[5],
                        'mRender':function(d,t,f) {
                            if(d == 1) return "Sim";
                            return "Não";
                        }
                    }
                    <?php } ?>
                ]
            });
        	$("#form-filter-document").submit(function(e) {
            	tbDocuments.ajax.reload();
                e.preventDefault();
                $('html, body').animate({
            		scrollTop: $('#data-table-basic').offset().top - 128
            	}, 1000);
            });
        	$("#form-register-box").submit(function(e) {
    			$("#modalRegisterBox").modal('hide');
    			swal({
    				title: "Aguarde...",
    			   	showConfirmButton: false,
    			   	allowOutsideClick: false,
    			   	onOpen: function() {
	    				swal.showLoading();
	    				// Registra a caixa
	    				$.post('./core/actions/registerBox.php', $("#form-register-box").serialize(), function(res) {
	    			  		if(res.ok) {
	    			       		swal("Registro realizado com sucesso!", "", "success");
	    			       		tbDocuments.ajax.reload();
	    			       		$("#box_number").val("");
	    			       		$("#box_corridor").val("");
	    			      		$("#box_tower").val("");
	    			       		$("#box_floor").val("");
	    			       		$("#box_department").val("");
	    			       	} else {
	    			       		swal("Erro ao realizar a operação", res.message, res.type);
	    			        }
	    			    }, 'json').fail(function(){
    			      		swal("Erro ao realizar a operação", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
	    				});
	    			}
    			});
				e.preventDefault();
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