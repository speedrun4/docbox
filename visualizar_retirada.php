<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/WithdrawalController.php");
include_once (dirname(__FILE__) . "/core/utils/Input.php");

use Docbox\control\WithdrawalController;
use Docbox\utils\Input;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;
use Docbox\model\WithdrawalStatus;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$withController = new WithdrawalController($db);
$withdrawal = $withController->getWithdrawalById(Input::getInt('r'), $user->getClient());

if($withdrawal == NULL || $withdrawal->getClient() != $user->getClient())  {
	header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
	<?php include('head.php'); ?>
	<link href="assets/plugins/datatables/media/css/dataTables.foundation.css" rel="stylesheet">
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Retiradas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Retiradas</a></li>
                            <li class="breadcrumb-item"><a href="listar_retiradas.php">Listar Retiradas</a></li>
                            <li class="breadcrumb-item active">Visualizar retirada</li>
                        </ol>
                    </div>
					<div class="col-md-6 col-4 align-self-center">
						<a href="imprimir_pedido_retirada.php?r=<?php echo $withdrawal->getId(); ?>" target='_blank' class="btn float-right hidden-sm-down btn-secondary"><i class="fa fa-print"></i> Imprimir</a>
					</div>
                </div>
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <!-- row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Visualizar retirada</h4>
                                <h6 class="card-subtitle"> Dados da retirada </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="pul_token" name="pul_token" type="hidden">

                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_box">Nº da retirada</label>
			                                        <input id='doc_box' name='doc_box' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $withdrawal->getNumber(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>

                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="req_date">Data</label>
		                                        <input id='req_date' name='req_date' type="date" class="form-control" value="<?php echo $withdrawal->getCreationDate()->format("Y-m-d"); ?>" disabled>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="req_time">Hora</label>
			                                        <input id='req_time' name='req_time' type="text" class="form-control" value="<?php echo $withdrawal->getCreationDate()->format("H:i"); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="req_user">Solicitante</label>
		                                        <input type="text" class="form-control" value="<?php echo $withdrawal->getUserRequested()->getName(); ?>" disabled>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="pul_status">Status</label>
		                                        <input id='pul_status' type="text" class="form-control" value="<?php
													$status = $withdrawal->getStatus();
													switch($status) {
														case WithdrawalStatus::OPEN: echo "EM ABERTO"; break;
														case WithdrawalStatus::CANCELLED: echo "CANCELADA"; break;
														case WithdrawalStatus::FINISHED: echo "FINALIZADA"; break;
													}
	                                            ?>" disabled>
		                                    </div>
										</div>
                                		<?php
		                                if($user->isAdmin()) {
		                                	if($withdrawal->getStatus() == WithdrawalStatus::OPEN) { ?>
			                                	<div id='rowFileInput' class='col-md-6'>
				                                    <div class="form-group">
				                                    	<div class='controls'>
						                                    <label>Comprovante digitalizado</label>
						                                    <input id='pul_file' name="pul_file" type="file" class="form-control" accept='.pdf,image/*' required data-validation-required-message="Selecione o arquivo comprovante">
				                                    	</div>
					                                </div>
				                                </div>
		                                <?php
												}
											}
											if($withdrawal->getStatus() == WithdrawalStatus::FINISHED) {
												if($user->isAdmin()) { ?>
													<div id='rowFileInput' class='col-md-6'>
														<div class="form-group">
															<div class='controls'>
																<label>Comprovante digitalizado</label>
																<input id='pul_file' name="pul_file" type="file" class="form-control" accept='.pdf,image/*' required data-validation-required-message="Selecione o arquivo comprovante">
															</div>
														</div>
													</div>
												<?php } ?>
												<div class='col-md-6'>
													<div class="form-group">
														<div class='controls'>
															<a href='<?= $withdrawal->getReceipt() ?>' target='_blank' class="btn float-right btn-success"><i class="fa fa-eye"></i>&nbsp;&nbsp;Comprovante</a>
														</div>
													</div>
												</div>
										<?php } ?>
                                		<div class='col-md-12'>
	                                		<?php
			                                if($withdrawal->getStatus() == WithdrawalStatus::OPEN) {
			                                    if($user->isAdmin()) { ?>
		        	                                <button id="btSendRequest" type="button" class="btn btn-primary m-t-10 float-right"> <i class='fa fa-paper-plane'></i> ENVIAR</button>
			                                    <?php }
			                                ?>
											<?php if($user->isAdmin() || $withdrawal->getUserRequested()->getId() == $user->getId()) { ?>
												<button id="btCancelRequest" type="button" class="btn btn-danger btn-icon-text m-t-10 float-left"> <i class='fa fa-trash'></i> CANCELAR</button>
											<?php }
											} else if($withdrawal->getStatus() == WithdrawalStatus::FINISHED) {
												if($user->isAdmin()) {?>
													<button id="btSendRequest" type="button" class="btn btn-primary m-t-10 float-right"> <i class='fa fa-sync'></i> Atualizar</button>
											<?php }
											} ?>
                                		</div>
                                	</div>
                                </form>
                            </div>
                        </div>
                    </div>
				</div>
				<?php if($withdrawal->getStatus() == WithdrawalStatus::OPEN) { ?>
					<div class='row'>
						<div class='col-sm-12'>
							<div class="card">
								<div class="card-body">
									<h4 class="card-title">Adicionar Documentos</h4>
									<h6 class="card-subtitle">Informe os dados do documento a ser adicionado</h6>
									<form id='form-add-document' class="mt-4" method='post' action='#' novalidate>
										<div class='row'>
											<div class='col-sm-6'>
												<div class="form-group">
													<div class='controls'>
														<label for="number">Número</label>
														<input type="number" class="form-control with_number" placeholder="" min='1' value=''>
													</div>
												</div>
											</div>
											<div class='col-sm-6'>
												<div class="form-group">
													<div class='controls'>
														<label for="year">Ano</label>
														<input type="number" class="form-control with_year" placeholder="" min='1900' value=''>
													</div>
												</div>
											</div>
										</div>
										<button type="submit" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Adicionar</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Documentos</h4>
								<h6 class="card-subtitle">Documentos pertencentes a retirada</h6>
								<?php if($withdrawal->getStatus() == WithdrawalStatus::OPEN) { ?>
								<button type="button" class="btn btn-warning" onclick="return removeFromWithdrawals()"><i class="fa fa-trash-alt"></i> Excluir selecionados</button>
								<?php } ?>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic"
										class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Número</th>
												<th>Ano</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Número</th>
												<th>Ano</th>
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
								<h6 class="card-subtitle">Lista o histórico da retirada</h6>
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
    <script type="text/javascript">
    var listDocuments = [];
	var tbDocuments = null;
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    $(function() {
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
    	tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"aaSorting": [2, "asc"],
        	"language" : _lang,
        	"lengthChange": false,
        	"paging":false,
        	"searching":false,
        	"sAjaxSource": "./core/data-tables/TableWithdrawalDocs.php",
        	"sServerMethod": "POST",
        	"fnServerParams": function(aoData) {
				aoData.push({"name": "r", "value" : <?php echo $withdrawal->getId(); ?>});
        	},
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}, {
                	"aTargets" : [1],
                	"width" : "60",
                	"orderable" : false,
					<?php if($withdrawal->getStatus() != WithdrawalStatus::OPEN) { ?>
					"visible": false,
					<?php } ?>
                	"mRender" : function(d,t,f) {
						<?php if($withdrawal->getStatus() == WithdrawalStatus::OPEN) { ?>
                		return '<div class="custom-control custom-checkbox mr-sm-2">' +
                        '<input data-box="' + f[0] + '" type="checkbox" class="check-request-add custom-control-input" id="check-box-' +
                         f[0] + '" name="check-box-' + f[0] + '" value="on" >' +
                        '<label class="custom-control-label" for="check-box-' + f[0] + '"></label>' +
                        '</div>';
						<?php } else { ?>
						return "";
						<?php } ?>
                	}
                	
                }
            ],
            "drawCallback" : function(settings) {
				// Reselecionar os escolhidos
				$(".check-request-add").each((i,e) => {
					var box = $(e).data().box;
					var checked = listDocuments.filter(function(value) {
						return (parseInt(value) == parseInt(box))
					}).length > 0;
					$(e).prop('checked', checked);
				});

				$(".check-request-add").change(function() {
					var box = $(this).data().box;
					if($(this).is(":checked")) {
						if(listDocuments.filter(function(value) {
								return (parseInt(value) == parseInt(box))
							}).length == 0) {
							listDocuments.push($(this).data().box);
						}
					} else {
						listDocuments = listDocuments.filter(function(value) { return parseInt(value) != parseInt(box); });
					}
				});
            }
        });

    	var tbHistory = $('#data-table-history').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"searching": false,
        	"aaSorting": [3, "asc"],
        	"language" : _lang,
        	"lengthChange":false,
        	"sAjaxSource": "./core/data-tables/TableWithdrawalHistory.php",
        	"sServerMethod": "POST",
        	"fnServerParams": function(aoData) {
				aoData.push({"name": "r", "value" : <?= $withdrawal->getId(); ?>});
        	},
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}
            ]
		});

		var formRegisterWithdrawal = function(e) {
			var arrNumbers = new Array();
			var arrYears = new Array();

			$(".with_number").each(function(index) {
				var n = parseInt($(this).val());
				if(n > 0) {
					arrNumbers.push(n);
				}
			});
			$(".with_year").each(function(index) {
				var n = parseInt($(this).val());
				if(n > 0) {
					arrYears.push(n);
				}
			});

			if(arrNumbers.length > 0 && arrYears.length > 0) {
				if(arrNumbers.length == arrYears.length) {
					// Verifica repetição de documentos
					var found = false;
					var data = tbDocuments.rows().every(function() {
						var d = this.data();
						for(var i = 0; i < arrNumbers.length; i++) {
							if(d[2] == arrNumbers[i] && d[3] == arrYears[i]) {
								found = true;
							}
						}
					});
					if(!found) {
						swal({
							title: 'Deseja realmente adicionar o documento a retirada?',
							type: 'question',
							showCancelButton: true,
							confirmButtonText: 'Sim',
							cancelButtonText: "Não",
							}).then((result) => {
								if(result.value) {
									$.post("./core/actions/addDocToWithDrawal.php", {r:<?= $withdrawal->getId() ?>,numbers: arrNumbers, years: arrYears}, function(res) {
										if(res.ok) {
											swal("Cadastro realizado com sucesso", "", "success").then(() => {
												tbDocuments.ajax.reload();
												$(".with_number").val("");
												$(".with_year").val("");
											});
										} else {
											swal("Erro ao realizar cadastro", "", "error");
										}
									}, "json").fail(function() {
										swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
									});
								}
							});
					} else {
						swal("O documento já se encontra na lista", "", "warning");
					}
				} else {
					swal("Lista possui pares imcompletos", "Verifique se todos os documentos possuem os dados de número e ano", "error");
				}
			} else {
				if(arrNumbers.length == 0 && arrYears.length == 0) {
					swal("Lista vazia", "Informe os dados de número e ano dos documentos e tente novamente", "warning");
				} else {
					swal("Lista possui pares imcompletos", "Verifique se todos os documentos possuem os dados de número e ano", "error");
				}
			}
			e.preventDefault();
		};
		$("#form-add-document").submit(formRegisterWithdrawal);

    	var runSave = function() {
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
	        		_data.append(0, $("#pul_file")[0].files[0]);
					_data.append('pul_id', <?= $withdrawal->getId() ?>);

	    			swal({
			        	title: "Aguarde...",
			        	showConfirmButton: false,
			        	allowOutsideClick: false,
			        	onOpen: function() {
			        		swal.showLoading();
							$.ajax({
								url: './core/actions/closeWithdrawal.php?&files',
									type: 'POST',
									data: _data,
									cache: false,
									dataType: 'json',
									processData: false, // Don't process the files
									contentType: false, // Set content type to false as jQuery will tell the server its a query string request
									success: function(data, textStatus, jqXHR) {
										if(data.ok) {
											$.post("./core/actions/closeWithdrawal.php", {r: <?= $withdrawal->getId() ?>, token: data.token},
												function(res) {
													swal(res.message, '', res.type).then(() => {
														document.location.reload(true);
													});
												}, "json").fail(function() {
													swal("Erro ao alterar a retirada", "", "error");
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
        		}
        	});
        };

        $("#btCancelRequest").click(function() {
			swal({
    			title: 'Deseja realmente cancelar a retirada?',
    			text: "",
    			type: 'question',
    			showCancelButton: true,
    			confirmButtonColor: '#bd2130',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			showLoaderOnConfirm: true
			}).then((result) => {
				if(result.value) {
					$.post("./core/actions/cancelWithdrawal.php?r=<?= $withdrawal->getId() ?>", {}, function(data) {
				        if(data.ok) {
	  			        	swal("Retirada cancelada com sucesso!", "", "success").then(() => {
								document.location.reload(true);
							  });
				        } else {
							swal(data.error, "", data.type);
				        }
				    }, 'json').fail(function(xhr, status, error) {
				        swal("Erro de comunicação com o servidor", "Por favor tente novamente mais tarde.", "error");
	  			    });
				}
			});
    	});
        <?php if($user->isAdmin()) { ?>
        $("#btSendRequest").click(function() {
        	if($("#pul_file").val() != "" && $("#pul_file")[0].files.length == 1) {
	            var fileOK = false;
	            var fileSizeInMB = parseFloat((($("#pul_file")[0].files[0].size / 1024) / 1024).toFixed(4)); // MB

		        if(fileSizeInMB <= <?= MAX_UPLOAD_MB ?>) {
		        	fileOK = true;
	        		$("#pul_file").parents(".form-group").removeClass("error");
			    } else {
			    	$("#pul_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>O arquivo deve ser menor que " + <?= MAX_UPLOAD_MB ?> + " Mb</li><li>");
		        	$("#pul_file").parents(".form-group").addClass("error").removeClass("validate");
				}

				if(fileOK) {
			      	runSave();
				}
        	} else {
            	$("#pul_file").parents(".form-group").find(".help-block").html("<ul role=\"alert\"><li>Selecione o comprovante de entrega.</li><li>");
	        	$("#pul_file").parents(".form-group").addClass("error").removeClass("validate");
            }
        });
        <?php } ?>
    });

	function removeFromWithdrawals() {
		if(listDocuments.length > 0) {
			swal({
				type : 'question',
				title: 'Remover documentos',
				text: 'Confirma a remoção dos documentos selecionados ?',
				showCancelButton: true,
				confirmButtonColor: '#fa1111',
				cancelButtonColor: '#aaa',
				confirmButtonText: 'Sim',
				cancelButtonText: "Não"
			}).then((result) => {
				if(result.value) {
					// Remove the selected docs
					$.post("./core/actions/removeDocsFromWithdrawal.php", {r: <?= $withdrawal->getId() ?>, documents: listDocuments},
						function(res) {
							if(res.ok) {
								tbDocuments.ajax.reload();
								listDocuments = [];
							}
						}, "json").fail(function() {
							swal("Erro ao realizar alteração", "Não foi possível remover os arquivos. Por favor tente novamente mais tarde.", "error");
						});
				}
			});
		} else {
			swal("Atenção", "Nenhum documento selecionado", "warning");
		}
	}
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>