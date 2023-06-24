<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/model/RequestType.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/RequestController.php");

use DocBox\model\RequestStatus;
use Docbox\control\RequestController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use Docbox\model\RequestType;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$reqController = new RequestController($db);
$request = $reqController->getRequest(intval($_GET['r']));

if($request == NULL || $request->getType() != RequestType::BOX || $request->getClient() != $user->getClient())  {
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php include('head.php'); ?>
    <link href="./js/components/infographic/style.css" rel="stylesheet"/>
    <style type="text/css">
    input:disabled {
        color: blue;
    }
    </style>
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Pedidos</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Pedidos</a></li>
                            <li class="breadcrumb-item"><a href="listar_pedidos.php">Listar Pedidos</a></li>
                            <li class="breadcrumb-item active">Visualizar pedido</li>
                        </ol>
                    </div>
                    <?php if($user->getProfile() == User::USER_ADMIN) { ?>
                    	<?php if($request->getStatus() == RequestStatus::RETURNING || $request->getStatus() == RequestStatus::COMPLETED) { ?>
                    		<div class="col-md-6 col-4 align-self-center">
		                        <button id='btShowModalReturns' type='button' class="btn float-right hidden-sm-down btn-success">
		                        	<i class="fa fa-print"></i> Devoluções</button>
		                    </div>
	                    <?php } else if($request->getStatus() != RequestStatus::COMPLETED && $request->getStatus() != RequestStatus::CANCELED) { ?>
		                    <div class="col-md-6 col-4 align-self-center">
		                        <a href="imprimir_pedido_caixas.php?r=<?php echo $request->getId(); ?>" target='_blank' class="btn float-right hidden-sm-down btn-secondary"><i class="fa fa-print"></i> Imprimir</a>
		                    </div>
	                    <?php } ?>

                    <?php } ?>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Visualizar pedido</h4>
                                <h6 class="card-subtitle"> Dados do pedido </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="req_token" name="req_token" type="hidden">

                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_box">Nº do pedido</label>
			                                        <input id='doc_box' name='doc_box' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $request->getNumber(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>

                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="req_date">Data</label>
		                                        <input id='req_date' name='req_date' type="date" class="form-control" value="<?php echo $request->getDatetime()->format("Y-m-d"); ?>" disabled>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="req_time">Hora</label>
			                                        <input id='req_time' name='req_time' type="text" class="form-control" value="<?php echo $request->getDatetime()->format("H:i"); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="req_user">Solicitante</label>
		                                        <input type="text" class="form-control" value="<?php echo $request->getUser()->getName(); ?>" disabled>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="req_status">Status</label>
		                                        <input id='req_status' type="text" class="form-control" value="<?php
		                                            $status = $reqController->getRequestStatus();
		                                            foreach($status as $s) {
		                                            	if($s->getId() == $request->getStatus()) {
		                                            		echo $s->getName(); break;
		                                            	}
		                                            }
	                                            ?>" disabled>
		                                    </div>
                                		</div>
                                		<?php
		                                if($user->isAdmin()) {
		                                	if($request->getStatus() == RequestStatus::SENT || $request->getStatus() == RequestStatus::RETURNED) { ?>
			                                	<div id='rowFileInput' class='col-md-6'>
				                                    <div class="form-group">
				                                    	<div class='controls'>
						                                    <label>Comprovante digitalizado</label>
						                                    <input id='req_file' name="req_file" type="file" class="form-control" accept='.pdf,image/*' required data-validation-required-message="Selecione o arquivo comprovante">
				                                    	</div>
					                                </div>
				                                </div>
		    	                            <?php if($request->getStatus() != RequestStatus::CANCELED) { ?>
		    	                                <!-- button id="btSaveRequestStatus" type="button" class="btn btn-primary btn-icon-text pull-right"> <i class="zmdi zmdi-save"></i>Salvar</button-->
		    	                            <?php } ?>
		                                <?php
												}
											}
										?>
										<div class='col-md-12'>
											<div id="rootInfographic" style="margin-top:60px; display:flex;flex-direction: column;justify-content: space-around;"></div>
										</div>
                                		<div class='col-md-12'>
	                                		<?php
			                                if($request->getStatus() == RequestStatus::OPENED) { // Cancelar // Marcar para envio
			                                    if($user->isAdmin()) { ?>
		        	                                <button id="btSendRequest" type="button" class="btn btn-primary btn-sm m-t-10 float-right"> <i class='fa fa-paper-plane'></i> ENVIAR</button>
			                                    <?php }
			                                ?>
			                                	<?php if($user->isAdmin() || $request->getUser()->getId() == $user->getId()) { ?>
		    	                                	<button id="btCancelRequest" type="button" class="btn btn-danger btn-icon-text btn-sm m-t-10 float-left"> <i class='fa fa-trash'></i> CANCELAR</button>
		    	                            	<?php } ?>
			                                <?php } else if($request->getStatus() == RequestStatus::SENT) {
			                                    if($user->isAdmin()) { ?>
			                                    <button id="btAttendedRequest" type="button" class="btn btn-success btn-sm m-t-10 float-right"> <i class='fa fa-check-circle'></i> MARCAR COMO ATENDIDO</button>
			                                <?php }
			                                } else if($request->getStatus() == RequestStatus::RETURNED) { ?>
			                                    <button id="btFinishRequest" type="button" class="btn btn-success btn-sm m-t-10 float-right"> <i class='fa fa-check'></i> FINALIZAR PEDIDO</button>
			                                <?php } else if($request->getStatus() == RequestStatus::ATTENDEND) { ?>
			                                    <button id="btReturnRequest" type="button" class="btn btn-success btn-sm m-t-10 float-right"> <i class='fa fa-arrow-right'></i> Devolver TODOS</button>
			                                    <button id="btReturnSelected" type="button" class="btn btn-primary btn-sm m-t-10 float-left"> <i class='fa fa-arrow-right'></i> Devolver SELECIONADOS</button>
			                                <?php } else if($request->getStatus() == RequestStatus::RETURNING) { ?>
			                                	<button id="btReturnSelected" type="button" class="btn btn-primary btn-sm m-t-10 float-left"> <i class='fa fa-arrow-right'></i> Devolver SELECIONADOS</button>
			                                <?php } ?>
                                		</div>
                                	</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Caixas</h4>
								<h6 class="card-subtitle">Caixas pertencentes ao pedido</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic"
										class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Departamento</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Departamento</th>
											</tr>
										</tfoot>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Histórico</h4>
								<h6 class="card-subtitle">Lista o histórico do pedido</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-history" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Usuário</th>
												<th>Ação</th>
												<th>Data</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>Usuário</th>
												<th>Ação</th>
												<th>Data</th>
											</tr>
										</tfoot>
										<tbody>
										</tbody>
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
    <?php include("modal_list_request_returns.php"); ?>
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
    <!-- Validation -->
    <script src="js/validation.js"></script>
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <script src="js/jasny-bootstrap.js"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>

    <script src="assets/plugins/react16/react.production.min.js"></script>
	<script src="assets/plugins/react16/react-dom.production.min.js"></script>
    <script type="text/javascript" src="js/components/infographic/InfoGraphic.js"></script>
    <script type="text/javascript" src="js/components/infographic/InfoGraphicPoint.js"></script>
    <script type="text/javascript" src="js/components/infographic/InfoGraphicLine.js"></script>
    <script type="text/javascript">
    var listReturnDocIds = [];
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    $(function() {
    	ReactDOM.render(React.createElement(InfoGraphic, {'request':<?= $request->getId(); ?>}), document.getElementById('rootInfographic'));
    	blockFormEnter('#form-register,#form-register-box');

    	var _lang = {
				"sEmptyTable": "Nenhum registro encontrado",
            	"sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            	"sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            	"sInfoFiltered": "",
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
			};
    	<?php
    	/**
    	 * O estado do pedido permite a devolução parcial ?
   		 * @var boolean $requestAllowsPartialDevolution
   		 */
   		$requestAllowsPartialDevolution = ($request->getStatus() == RequestStatus::ATTENDEND || $request->getStatus() == RequestStatus::RETURNING);
    	?>
    	var shouldShowCheckAll = false;
    	var tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"aaSorting": [2, "asc"],
        	"language" : _lang,
        	"lengthChange": false,
        	"paging":false,
        	"searching":false,
        	"sAjaxSource": "./core/data-tables/TableRequestBoxes.php",
        	"sServerMethod": "POST",
        	"fnServerParams": function(aoData) {
				aoData.push({"name": "r", "value" : <?php echo $request->getId(); ?>});
        	},
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}, {
                	"aTargets" : [1],
                	"width" : "60",
                	"orderable" : false,
                	<?php if($requestAllowsPartialDevolution) { ?>
                	"mRender" : function(d,t,f) {
                    	if(d == <?= RequestStatus::COMPLETED?> || d == <?= RequestStatus::RETURNED ?>) {
                        	return "";
                    	}

                    	shouldShowCheckAll = true;

                		return '<div class="custom-control custom-checkbox mr-sm-2">' +
                        '<input data-box="' + f[0] + '" type="checkbox" class="check-request-add custom-control-input" id="check-box-' +
                         f[0] + '" name="check-box-' + f[0] + '" value="on" >' +
                        '<label class="custom-control-label" for="check-box-' + f[0] + '"></label>' +
                        '</div>';
                	}
                	<?php } else { ?>
                	"visible":false
                	<?php } ?>
                }, {
                    "aTargets": [2],
                    className: 'dt-body-center',
                    'mRender': function(d,t,f) {
                    	return "<a href='visualizar_caixa.php?box=" + f[0] + "'>" + d + "</a>";
                    }
                }
            ],
            "drawCallback" : function(settings) {
                if(!shouldShowCheckAll) {
                    // Some com a coluna
                    tbDocuments.column(1).visible(false);
                    $("#btReturnSelected").hide();
                }
            	<?php if($requestAllowsPartialDevolution) { ?>
		            // Reselecionar os escolhidos
	            	$(".check-request-add").each((i,e) => {
	            		var box = $(e).data().box;
		            	var checked = listReturnDocIds.filter(function(value) {
		        			return (parseInt(value) == parseInt(box))
		        		}).length > 0;
		        		$(e).prop('checked', checked);
	            	});

	            	$(".check-request-add").change(function() {
	                	var box = $(this).data().box;
	                	if($(this).is(":checked")) {
	                		if(listReturnDocIds.filter(function(value) {
	                    			return (parseInt(value) == parseInt(box))
	                    		}).length == 0) {
	                			listReturnDocIds.push($(this).data().box);
	                		}
	                	} else {
	                		listReturnDocIds = listReturnDocIds.filter(function(value) { return parseInt(value) != parseInt(box); });
	                	}
	                });
	    		<?php } ?>
            }
        });

    	var tbHistory = $('#data-table-history').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"searching": false,
        	"aaSorting": [3, "asc"],
        	"language" : _lang,
        	"lengthChange":false,
        	"sAjaxSource": "./core/data-tables/TableRequestHistory.php",
        	"sServerMethod": "POST",
        	"fnServerParams": function(aoData) {
				aoData.push({"name": "r", "value" : <?php echo $request->getId(); ?>});
        	},
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}
            ]
        });

    	var tbReturns = $('#data-table-returns').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"searching": false,
        	"aaSorting": [3, "asc"],
        	"language" : _lang,
        	"lengthChange":false,
        	"sAjaxSource": "./core/data-tables/TableReqBoxesDevolutions.php",
        	"sServerMethod": "POST",
        	"fnServerParams": function(aoData) {
						aoData.push({"name": "r", "value" : <?php echo $request->getId(); ?>});
        	},
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"mRender" : function(d,t,f) {
                    	return "<a href='imprimir_devolucao.php?dev=" + d + "' target='_blank' class='btn btn-circle btn-primary'><i class='fa fa-print'></i></a>";
                	}
            	}, {
                	'aTargets':[4],
                	'mRender' : function(d,t,f) {
                    	if(d == "" || d == undefined || d == null) {
                    		return "<div class='col-md-12'>"+
                    				"<div class='form-group'>"+
                    					"<div class='controls'><label>Comprovante digitalizado</label>"+
                        					"<input id='rowFileInput_" + f[0] + "' data-return_id='" + f[0] + "' class='rowFileInput form-control' accept='.pdf,image/*' type='file' required data-validation-required-message='Selecione o arquivo comprovante'>"+
                        					"<div class='help-block'></div>"+
                        				"</div>"+
                        			"</div>"+
                        		"</div>";
                    	}
                    	return "<a target='_blank' href='devolution_files/" + d + "' class='btn btn-circle btn-success'><i class='fa fa-file'></i></a>"+
                    			"<button data-devolution='" + f[0] + "' type='button' class='btRemoveDevolutionFile btn btn-circle btn-danger'><i class='fa fa-trash'></i></button>";
                	}
            	}
            ],
            'drawCallback' : function(settings) {
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

        <?php if($request->getUser()->getId() == $user->getId()) { ?>
        	<?php if($request->getStatus() == RequestStatus::ATTENDEND || $request->getStatus() == RequestStatus::RETURNING) { ?>
		        $("#btReturnSelected").click(function() {
		        	if(listReturnDocIds.length > 0) {
		    			swal({
			        		type : 'question',
			        		title: 'Devolução de pedido',
			        		text: 'Confirma a devolução dos documentos selecionados?',
			        		showCancelButton: true,
			        		confirmButtonColor: '#3085d6',
		        			cancelButtonColor: '#aaa',
		        			confirmButtonText: 'Sim',
		        			cancelButtonText: "Não"
			        	}).then((result) => {
				        	if(result.value) {
		    	        		swal({
		    			        	title: "Aguarde...",
		    			        	showConfirmButton: false,
		    			        	allowOutsideClick: false,
		    			        	onOpen: function() {
		    			        		swal.showLoading();
		    			        		$.post("./core/actions/registerRequestDevolution.php", {'request' : <?= $request->getId() ?> , "ids" : listReturnDocIds},
				    			        	function(data) {
			    		  			        	if(data.ok) {
			    		  			        		swal("Devolução solicitada", "A devolução foi solicitada com sucesso!", "success").then(()=> {
				    		  			        		window.location.reload();
			    		  			        		});
			    		  			        	} else {
			    									swal(data.error, "", data.type);
			    		  			        	}
			    		  			        }, 'json').fail(function(xhr, status, error) {
			    		  			        	swal("Erro ao realizar ação", "Por favor verifique sua conexão com a internet, e tente novamente mais tarde.", "error");
			    		      			    });
		    			        	}
		    		        	});
				        	}
		    	        });
		        	} else {
		        		swal("Atenção", "Nenhum documento selecionado!", "warning");
		        	}
				});
	        <?php } ?>
        <?php } ?>

    	var runSave = function(status) {
        	swal({
        		type : 'question',
        		title: 'Salvar alterações',
        		text: 'Deseja realmente salvar as modificações no pedido?',
        		showCancelButton: true,
        		confirmButtonColor: '#3085d6',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não"
        	}).then((result) => {
            	if(result.value) {
	            	var _data = new FormData();
	        		_data.append(0, $("#req_file")[0].files[0]);
	    			swal({
			        	title: "Aguarde...",
			        	showConfirmButton: false,
			        	allowOutsideClick: false,
			        	onOpen: function() {
			        		swal.showLoading();
    	        		$.ajax({
    	        			url: './core/actions/changeRequestStatus.php?&files',
				        		type: 'POST',
					        	data: _data,
					        	cache: false,
					        	dataType: 'json',
					        	processData: false, // Don't process the files
					        	contentType: false, // Set content type to false as jQuery will tell the server its a query string request
					        	success: function(data, textStatus, jqXHR) {
						        	if(data.ok) {
							        	$("#req_token").val(data.token);
							        	changeRequestStatus(status);
							    		} else {
							    			swal("", "Erro ao realizar upload dos arquivos", "error");
											}
						    		}, error: function(jqXHR, textStatus, errorThrown) {
					            swal("", "Erro ao realizar upload dos arquivos", "error");
					        	}
        	    		});
		        		}
	        		});
        		}
        	});
        };

        $("#btCancelRequest").click(function() {
			swal({
    			title: 'Deseja realmente cancelar o pedido?',
    			text: "",
    			type: 'question',
    			showCancelButton: true,
    			confirmButtonColor: '#3085d6',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			showLoaderOnConfirm: true
			}).then((result) => {
				if(result.value) {
					$.post("./core/actions/cancelRequest.php?r=<?php echo intval($_GET['r']);  ?>", {}, function(data) {
				        if(data.ok) {
	  			        	swal("Pedido cancelado com sucesso!", "", "success");
	  			        	$("#req_status").val("CANCELADO");
	  			        	$("#btCancelRequest").hide();
	  			        	tbHistory.ajax.reload();
				        } else {
							swal(data.error, "", data.type);
				        }
				    }, 'json').fail(function(xhr, status, error) {
				        swal("Erro ao realizar pedido", "Por favor tente novamente mais tarde.", "error");
	  			    });
				}
			});
    	});
        <?php if($user->isAdmin()) {?>
        <?php if($request->getStatus() == RequestStatus::RETURNING || $request->getStatus() == RequestStatus::COMPLETED) { ?>
	        $('#btShowModalReturns').click(function() {
		        $("#modalListReturns").modal();
	        });
	    <?php } ?>
            <?php if($request->getStatus() == RequestStatus::ATTENDEND) { ?>
            $("#btReturnRequest").click(function() {
            	swal({
	        		type : 'question',
	        		title: 'Devolução de pedido',
	        		text: 'Confirma a devolução do pedido?',
	        		showCancelButton: true,
	        		confirmButtonColor: '#3085d6',
        			cancelButtonColor: '#aaa',
        			confirmButtonText: 'Sim',
        			cancelButtonText: "Não"
	        	}).then((result) => {
		        	if(result.value) {
		        		swal({
				        	title: "Aguarde...",
				        	showConfirmButton: false,
				        	allowOutsideClick: false,
				        	onOpen: function() {
				        		swal.showLoading();
	            	        	changeRequestStatus(<?php echo RequestStatus::RETURNED; ?>, () => {
	                	        	$("#req_status").val("DEVOLVIDO");
	                	        	$("#btReturnRequest").hide();
	                	        	tbHistory.ajax.reload();
	                	        });
				        	}
			        	});
			        }
    	        });
            });
        <?php } ?>
        $("#btSendRequest").click(function() {
        	swal({
        		type : 'question',
        		title: 'Salvar alterações',
        		text: 'Deseja realmente salvar as modificações no pedido?',
        		showCancelButton: true,
        		confirmButtonColor: '#3085d6',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não"
        	}).then((result) => {
            	if(result.value) {
	        		swal({
			        	title: "Aguarde...",
			        	showConfirmButton: false,
			        	allowOutsideClick: false,
			        	onOpen: function() {
			        		swal.showLoading();
	        	        	changeRequestStatus(<?php echo RequestStatus::SENT; ?>, () => {
	            	        	$("#req_status").val("EM ENVIO");
	            	        	$("#btSendRequest").hide();
	            	        	$("#btCancelRequest").hide();
	            	        	tbHistory.ajax.reload();
	            	        });
			        	}
		        	});
            	}
	        });
        });

        $("#btAttendedRequest").click(function() {
        	if($("#req_file").val() != "" && $("#req_file")[0].files.length == 1) {
	            var fileOK = false;
	            var fileSizeInMB = parseFloat((($("#req_file")[0].files[0].size / 1024) / 1024).toFixed(4)); // MB
		        if(fileSizeInMB <= MAX_UPLOAD_SIZE) {
		        	fileOK = true;
	        		$("#req_file").parents(".form-group").removeClass("error");
			    } else {
			    	$("#req_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb</li><li>");
		        	$("#req_file").parents(".form-group").addClass("error").removeClass("validate");
				}
				if(fileOK) {
			      	runSave(<?php echo RequestStatus::ATTENDEND; ?>, () => {
						$("#req_status").val("ATENDIDO");
						$("#rowFileInput").hide();
						$("#btAttendedRequest").hide();
						tbHistory.ajax.reload();
	        	    });
				}
        	} else {
            	$("#req_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>Selecione o comprovante de entrega.</li><li>");
	        	$("#req_file").parents(".form-group").addClass("error").removeClass("validate");
            }
        });

        $("#btFinishRequest").click(function() {
        	if($("#req_file").val() != "" && $("#req_file")[0].files.length == 1) {
            	var fileOK = false;
            	var fileSizeInMB = parseFloat((($("#req_file")[0].files[0].size / 1024) / 1024).toFixed(4)); // MB
	        	if(fileSizeInMB <= MAX_UPLOAD_SIZE) {
	        		fileOK = true;
	        		$("#req_file").parents(".form-group").removeClass("error");
		        } else {
		        	$("#req_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + MAX_UPLOAD_SIZE + "Mb</li><li>");
		        	$("#req_file").parents(".form-group").addClass("error").removeClass("validate");
			    }
			    if(fileOK) {
		        	runSave(<?php echo RequestStatus::COMPLETED; ?>,
    	    		    () => {
        		        	$("#req_status").val("FINALIZADO");
        		        	$("#rowFileInput").hide();
        		        	$("#btFinishRequest").hide();
        		        	tbHistory.ajax.reload();
        		        });
				}
            } else {
            	$("#req_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>Selecione o comprovante de entrega.</li><li>");
	        	$("#req_file").parents(".form-group").addClass("error").removeClass("validate");
            }
        });
        <?php } else { ?>
            $("#btReturnRequest").click(function() {
            	swal({
	        		type : 'question',
	        		title: 'Devolução de pedido',
	        		text: 'Confirma a devolução do pedido?',
	        		showCancelButton: true,
	        		confirmButtonColor: '#3085d6',
        			cancelButtonColor: '#aaa',
        			confirmButtonText: 'Sim',
        			cancelButtonText: "Não"
	        	}).then((result) => {
	        		swal({
			        	title: "Aguarde...",
			        	showConfirmButton: false,
			        	allowOutsideClick: false,
			        	onOpen: function() {
			        		swal.showLoading();
            	        	changeRequestStatus(<?php echo RequestStatus::RETURNED; ?>, () => {
                	        	$("#req_status").val("DEVOLVIDO");
                	        	$("#btReturnRequest").hide();
                	        	tbHistory.ajax.reload();
                	        });
			        	}
		        	});
    	        });
            });
        <?php } ?>
    });
    function changeRequestStatus(status) {
    	$.post('./core/actions/changeRequestStatus.php', {r:<?php echo $request->getId();?>, s: status,token:$("#req_token").val()},
            function(res) {
	        	if(res.ok) {
		        	if(res.action == 'print') {
		        		swal({
                			title: res.response,
                    		text: "Você será direcionado para página de impressão do comprovante de entrega",
                    		type: res.type
                    	}).then((result) => {
                        	if(result.value) {
	                    		var win = window.open("imprimir_pedido_caixas.php?r=<?php echo $request->getId(); ?>", '_blank');
	                    		if (win) { //Browser has allowed it to be opened
	                    		    win.focus();
															document.location.reload(true);
	                    		} else { //Browser has blocked it
	                    		    alert('Por favor habilite a exibição de popups para o site!');
	                    		}
                        	}
                    	});
		        	} else {
								swal(res.response, '', res.type).then(() => {
		        			document.location.reload(true);
		        		});
			        }
		        } else {
        			swal(res.response, '', res.type);
            	}
            }, 'json').fail(function(xhr, status, error) {
	        	swal("Erro ao realizar alteração", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
		    });
    }
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>
