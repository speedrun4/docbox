<?php
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/utils/Input.php");
include_once (dirname(__FILE__) . "/core/model/Request.php");
include_once (dirname(__FILE__) . "/core/model/RequestType.php");
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
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

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$usrController = new UserController($db);
$reqController = new RequestController($db);
$doctypeController = new DocumentTypeController($db);

$filterOpened = Input::getInt('f') == 5;
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
                        <h3 class="text-themecolor mb-0 mt-0">Devoluções</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Devoluções</a></li>
                            <li class="breadcrumb-item active">Listar Devoluções</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
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
                                <h6 class="card-subtitle">Utilize os filtros abaixo para pesquisar nas devoluções</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Status</label>
		                                            <select id='dev_status' name='dev_status' class="form-control">
		                                                <option value="" selected>Selecione...</option>
		                                                <option value="1" <?= $filterOpened ? "selected" : "" ?>>EM ABERTO</option>
		                                                <option value="2">CONCLUÍDA</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="dev_number">N&ordm; da devolução</label>
			                                        <input id='dev_number' name='dev_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="dev_date_from">Data inicial</label>
		                                        <input id='dev_date_from' name='dev_date_from' type="date" class="form-control" value="<?php echo isset($date_from) ? $date_from : ""; ?>">
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="dev_date_to">Data final</label>
		                                        <input id='dev_date_to' name='dev_date_to' type="date" class="form-control" value='<?php echo isset($date_to) ? $date_to : ""; ?>'>
		                                    </div>
                                		</div>
                                    	<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Usuário</label>
		                                            <select id='dev_user' name='dev_user' class="form-control">
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
                                    	<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_number">N&ordm; do documento</label>
			                                        <input id='doc_number' name='doc_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="req_number">N&ordm; do pedido</label>
			                                        <input id='req_number' name='req_number' type="number" class="form-control" placeholder="0" min='1' value=''>
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
                                <h6 class="card-subtitle">Esta tabela exibe todos as devoluções cadastradas gerenciados pelo Web Software conforme os filtros indicados.</h6>
                                <div class="table-responsive m-t-40">
                                    <table id="data-table-basic" class="table table-bordered table-striped">
                                    	<thead>
		                                <tr>
		                                	<th>Ações</th>
		                                    <th>N&ordm;</th>
		                                    <th>Solicitante</th>
		                                    <th>Data/Hora</th>
		                                    <th>Comprovante</th>
		                                </tr>
		                                </thead>
		                                <tfoot>
		                                <tr>
		                                    <th>Ações</th>
		                                    <th>N&ordm;</th>
		                                    <th>Solicitante</th>
		                                    <th>Data/Hora</th>
		                                    <th>Comprovante</th>
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
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(document).ready(function() {
        	var tbRequests = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [1, "desc"],
            	"language" : {
            	    "sEmptyTable": "Nenhuma devolução encontrada",
            	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ devoluções",
            	    "sInfoEmpty": "Mostrando 0 até 0 de 0 devoluções",
            	    "sInfoFiltered": "(Filtrados de _MAX_ devoluções)",
            	    "sInfoPostFix": "",
            	    "sInfoThousands": ".",
            	    "sLengthMenu": "_MENU_ resultados por página",
            	    "sLoadingRecords": "Carregando...",
            	    "sProcessing": "Processando...",
            	    "sZeroRecords": "Nenhuma devolução encontrada",
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
            	"sAjaxSource": "./core/data-tables/TableDevolutions.php",
            	"sServerMethod" : "POST",
            	"fnServerParams" : function(aoData) {
            		aoData.push(
        				{"name": "dev_number", "value" : $("#dev_number").val()},
        				{"name": "doc_number", "value" : $("#doc_number").val()},
        				{"name": "req_number", "value" : $("#req_number").val()},
        				{"name": "dev_date_from", "value" : $("#dev_date_from").val()},
        				{"name": "dev_date_to", "value" : $("#dev_date_to").val()},
        				{"name": "dev_user", "value" : $("#dev_user").val()},
        				{"name": "dev_status", "value" : $("#dev_status").val()},
        				{"name": "req_open_dev", "value" : $("#req_open_dev").val()});
            	},
            	"aoColumnDefs" : [
                	{
	                	"aTargets": [0],
	                	"mRender" : function(d,t,f) {
											let content = "";
											let url = "";

											if(f[5] == <?= RequestType::DOCUMENT ?>) {
												url = "visualizar_devolucao_documentos.php";
											} else if(f[5] == <?= RequestType::BOX ?>) {
												url = "visualizar_devolucao_caixas.php";
											}
											if(url != "") {
												content += `<a href='${url}?dev=${d}' class='btn btn-circle btn-success' ><i class='fa fa-eye'></i></a>`;
											}
	                		<?php if($user->isAdmin()) { ?>
                    	content += "<a href='imprimir_devolucao.php?dev=" + d + "' target='_blank' class='btn btn-circle btn-primary'><i class='fa fa-print'></i></a>";
											<?php } ?>
                    	return content;
	                	}
	            	}, {
                    	'aTargets':[4],
                    	'className': 'dt-body-center',
                    	'mRender' : function(d,t,f) {
                        	if(d == "" || d == undefined || d == null) {
                            	<?php if($user->isAdmin()) { ?>
                        		return "<div class='col-md-12'>"+
                        				"<div class='form-group'>"+
                        					"<div class='controls'><label>Comprovante digitalizado</label>"+
                            					"<input id='rowFileInput_" + f[0] + "' data-return_id='" + f[0] + "' class='rowFileInput form-control' accept='.pdf,image/*' type='file' required data-validation-required-message='Selecione o arquivo comprovante'>"+
                            					"<div class='help-block'></div>"+
                            				"</div>"+
                            			"</div>"+
                            		"</div>";
                        		<?php } else { ?>
                        		return "";
                        		<?php } ?>
                        	}
                        	// Bt view
                        	var content = "<a target='_blank' href='devolution_files/" + d + "' class='btn btn-circle btn-success'><i class='fa fa-file'></i></a>";
                        	<?php if($user->isAdmin()) { ?>
                        	content += "<button data-devolution='" + f[0] + "' type='button' class='btRemoveDevolutionFile btn btn-circle btn-danger'><i class='fa fa-trash'></i></button>";
                        	<?php } ?>
                        	return content;
                    	}
                	}
                ],
                "drawCallback" : function(settings) {
                	$(".rowFileInput").change(function() {
                        var returnData = $(this).data().return_id;
                        var btFileInput = $(this);
                        if($(this).val() != "" && $(this)[0].files.length == 1) {
            	        	if(belowMaxUploadSize($(this).prop('id'))) {
            	        		$(this).parents(".form-group").removeClass("error");
            	        		swal({
                                    type:'question',
                                    title:'Finalizar devolução',
                                    text: 'Deseja realmente incluir o arquivo e finalizar a devolução dos documentos?',
                                    showCancelButton: true,
                                    cancelButtonText: 'Não',
                                    confirmButtonText:'Sim'
                                }).then((result) => {
                                    if(result.value) {
            	                        swal.showLoading();
            	                        var _data = new FormData();
            	    	        		_data.append(0, btFileInput[0].files[0]);
            	                        $.ajax({
            	    	        			url: './core/actions/finishRequestReturn.php?&files',
            					        	type: 'POST',
            						        data: _data,
            						        cache: false,
            						        dataType: 'json',
            						        processData: false, // Don't process the files
            						        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            						        success: function(data, textStatus, jqXHR) {
            							        if(data.ok) {
            							        	$.post('./core/actions/finishRequestReturn.php', {ret: returnData, token : data.token}, function(data) {
            					                        if(data.ok) {
            					                        	swal("Devolução concluída com sucesso!", "", "success").then(function() {
                					                        	window.location.reload();
                					                        });
            					                        } else {
            					                        	swal("Não foi possível realizar a ação", "", "error");
            					                        }
            				                        }, 'json').fail(function() {
            				                        	swal("Erro ao realizar ação", "Por favor verifique sua conexão com a internet e tente novamente mais tarde.", "error");
            				                        });
            								    } else {
            								    	swal("", "Erro ao realizar upload dos arquivos", "error");
            									}
            							    }, error: function(jqXHR, textStatus, errorThrown) {
            						            swal("", "Erro ao realizar upload dos arquivos", "error");
            						        }
            	        	        	});
                                    }
                                });
            	        	} else {
            		        	$(this).parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb</li><li>");
            		        	$(this).parents(".form-group").addClass("error").removeClass("validate");
            			    }
                        } else {
                        	$(this).parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>Selecione o comprovante de entrega.</li><li>");
                        	$(this).parents(".form-group").addClass("error").removeClass("validate");
                        }
                    });
                	$(".btRemoveDevolutionFile").click(function() {
                    	var devolution_id = $(this).data().devolution;
                    	if(devolution_id > 0) {
                    		swal({
                                title: "Remover comprovante",
                                type: "question",
                                text: "Deseja realmente excluir o comprovante de devolução?",
                                showCancelButton: true,
                                cancelButtonText: "Não",
                                confirmButtonText: "Sim",
                                confirmButtonColor: "red"
                            }).then((result) => {
                                if(result.value) {
                                    swal.showLoading();
                                    $.get("./core/actions/deleteDevolutionFile.php", {devolution:devolution_id},
                                        function(res) {
    	                                    if(res.ok) {
    			                                swal({title: "Arquivo excluído com sucesso", type:"success"}).then(() => {
    			                                    window.location.reload();
    			                                });
    	                                    } else {
    		                                    swal("Erro", "Não foi possível realizar a operação", "error");
    	                                    }
                                        }, "json").fail(function() {
                                        	swal("Erro ao realizar ação", "Por favor verifique sua conexão com a internet, e tente novamente mais tarde.", "error");
                                        });
                                }
                              });
                        }
                    });
                }
            });
        	$("#form-filter-document").submit(function(e) {
            	tbRequests.ajax.reload();
                e.preventDefault();
                $('html, body').animate({
            		scrollTop: $('#data-table-basic').offset().top - 300
            	}, 1000);
            });
            <?php if($filterOpened) {?>
            $('html, body').animate({
        		scrollTop: $('#data-table-basic').offset().top - 300
        	}, 1000);
            <?php } ?>
        });
    </script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>
