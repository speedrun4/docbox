<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$doctypeController = new DocumentTypeController($db);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php include('head.php'); ?>
    <link href="assets/plugins/datatables/media/css/dataTables.foundation.css" rel="stylesheet">
	<style type="text/css">
	/* Esconder o campo de busca do DataTables */
	.dataTables_filter {
		display: none;
	}
	</style>
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Devoluções</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Devoluções</a></li>
                            <li class="breadcrumb-item active">Devolver caixas</li>
                        </ol>
                    </div>
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
                                <h4 class="card-title">Filtros</h4>
                                <h6 class="card-subtitle">Utilize os campos abaixo para filtrar os resultados da tabela.</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
																		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_number">Nº da caixa</label>
			                                        <input id='box_number' name='box_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                    	<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="doc_number">Nº documento</label>
		                                        <input id='doc_number' name='doc_number' type="number" class="form-control" placeholder="0" min='1'>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_year">Ano</label>
			                                        <input id='doc_year' name='doc_year' type="number" class="form-control" placeholder="" min='1900' max='9999'>
		                                    	</div>
		                                    </div>
                                		</div>

                                		<div class='col-md-4'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                                <label class="control-label">Tipo de documento</label>
		                                            <select id='doc_type' name='doc_type' class="form-control">
		                                                <option value="" selected>Selecione...</option>
		                                                    <?php
		                                                    $doctypes = $doctypeController->getTypes($user->getClient());
		                                                    foreach ($doctypes as $doctype) {
		                                                    	echo "<option value='" . $doctype->getId() . "'>" . $doctype->getDescription() . "</option>";
		                                                    }
		                                                    ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Letra</label>
                                                <select id='doc_letter' class="form-control custom-select">
                                                	<option value=""></option>
	                                            	<?php
														for ($i = 1; $i < 27; $i++) {
															echo "<option value='" . chr($i + 96) . "'>&nbsp;&nbsp;&nbsp;" . chr($i + 64) . "</option>";
														}
													?>
                                                </select>
                                            </div>
                                		</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Selecionados</label>
                                                <select id='doc_marked' name='doc_marked' class="form-control custom-select">
                                                	<option value="0" selected>TODOS</option>
                                                    <option value="1">Selecionados</option>
                                                    <option value="2">Não selecionados</option>
                                                </select>
                                            </div>
                                		</div>
                                		<div class='col-md-6'>
		                                    <div class="form-group">
		                                        <label for="doc_title">Título</label>
		                                        <input id='doc_title' name='doc_title' type="text" class="typeahead form-control" autocomplete="off">
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="doc_date_from">Data inicial</label>
		                                        <input id='doc_date_from' name='doc_date_from' type="date" class="form-control">
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="doc_date_to">Data final</label>
		                                        <input id='doc_date_to' name='doc_date_to' type="date" class="form-control">
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
								<h4 class="card-title">Caixas</h4>
								<h6 class="card-subtitle">Marque as caixas que deseja devolver</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>#</th>
												<th>Nº Pedido</th>
												<th>Nº Caixa</th>
												<th>Departamento</th>
												<th>Selada</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>#</th>
												<th>Nº Pedido</th>
												<th>Nº Caixa</th>
												<th>Departamento</th>
												<th>Selada</th>
											</tr>
										</tfoot>
										<tbody>
										</tbody>
									</table>
								</div>
								<div class='row'>
									<div class='col-md-12'>
										<button id='btConfirmDevolution' class='btn btn-primary m-t-10 float-right'>Realizar devolução</button>
									</div>
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
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <script src="js/typeahead.js/typeahead.bundle.js"></script>
    <script type="text/javascript">
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
	var listBoxesRequested = [];
    $(function() {
    	var _language = {
        	    "sEmptyTable": "Nenhuma caixa adicionada",
        	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        	    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
        	    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
        	    "sInfoPostFix": "",
        	    "sInfoThousands": ".",
        	    "sLengthMenu": "_MENU_ resultados por página",
        	    "sLoadingRecords": "Carregando...",
        	    "sProcessing": "Processando...",
        	    "sZeroRecords": "Nenhuma caixa adicionada",
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
        	};
        	var tbBoxes = $('#data-table-basic').DataTable({
            	"serverSide" : true,
            	"processing" : true,
            	"aaSorting" : [1, "asc"],
            	"language" : _language,
            	"sAjaxSource" : "./core/data-tables/TableBoxesRequested.php",
            	"sServerMethod" : "POST",
              "fnServerParams" : function(aoData) {
		    				aoData.push (
		    					{"name": "box_number", "value" : $("#box_number").val()},
		    					{"name": "doc_year", "value" : $("#doc_year").val()},
		    					{"name": "doc_number", "value" : $("#doc_number").val()},
		    					{"name": "doc_letter", "value" : $("#doc_letter").val()},
		    					{"name": "doc_type", "value" : $("#doc_type").val()},
		    					{"name": "doc_location", "value" : $("#doc_location").val()},
		    					{"name": "doc_date_from", "value" : $("#doc_date_from").val()},
		    					{"name": "doc_date_to", "value" : $("#doc_date_to").val()},
		    					{"name": "doc_title", "value" : $("#doc_title").val()},
		    					{"name": "doc_marked", "value" : $("#doc_marked").val()},
		    					{"name": "doc_in_req", "value" : 1},
		    					{"name": "doc_marks", 'value': listBoxesRequested}
		    				);
            	},
            	"drawCallback" : function(settings) {
                	$(".check-request-add").change(function() {
                    	var doc = $(this).data().doc;
                    	if($(this).is(":checked")) {
                    		if(listBoxesRequested.filter(function(value) { return (parseInt(value) == parseInt(doc)) }).length == 0) {
                        		listBoxesRequested.push($(this).data().doc);
                    		}
                    	} else {
                     		listBoxesRequested = listBoxesRequested.filter(function(value) { return parseInt(value) != parseInt(doc); });
                    	}
                    });
                },
            	"aoColumnDefs" : [{
            				"aTargets": [0],
										"width" : 24,
            				"orderable":false,
	            			'mRender':function(d,t,f) {
	            				// Se já está em devolução
            					if(f[7] > 0 || f[7] > 0) {
                      	return "";
                      }
                      let checked = '';
	            				if(listBoxesRequested.filter(function(value) { return (parseInt(value) == parseInt(f[0])) }).length > 0) {
	            					checked = 'checked';
	            				}
        							return '<div class="custom-control custom-checkbox mr-sm-2"><input data-doc="' + f[0] + '" type="checkbox" class="check-request-add custom-control-input" id="check-doc-' + f[0] + '" name="check-doc-' + f[0] + '" value="on" '+ checked +'><label class="custom-control-label" for="check-doc-' + f[0] + '"></label></div>';
	            			}
                	}, {
										"aTargets" : [1, 2],
										'visible' : false
									}, {
										"aTargets": [3],
										"width": 80,
										"mRender": function (d,t,f) {
											return "<a href='visualizar_pedido_caixas.php?r=" + f[2] + "'>" + d + "</a>";
										}
									}, {
										"aTargets": [4],
										"width": 80,
										"mRender": function(d,t,f) {
											return "<a href='visualizar_caixa.php?box=" + f[1] + "'>" + d + "</a>";
										}
                  }, {
										"aTargets" : [6],
										"visible" : <?= $user->isAdmin() ? "true" : "false" ?>,
										"mRender" : function(d,t,f) {
											return d == 1 ? "Sim" : "Não";
										}
									}
        		]
        });
        $("#form-filter-document").submit(function(e) {
           	tbBoxes.ajax.reload();
            e.preventDefault();
            $('html, body').animate({
        		scrollTop: $('#data-table-basic').offset().top - 128
        	}, 1000);
        });
        $("#btConfirmDevolution").click(function() {
					if(listBoxesRequested.length > 0) {
						swal({
		        			title: 'Deseja realmente realizar a devolução das caixas?',
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
			    				$.post("./core/actions/registerBoxDevolution.php", {"ids" : listBoxesRequested}, function(data) {
			  			        	if(data.ok) {
			  			        		window.location.href = "visualizar_devolucao_caixas.php?dev=" + data.dev_id;
			  			        	} else {
													swal(data.error, "", data.type);
			  			        	}
			  			        }, 'json').fail(function(xhr, status, error) {
			  			        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
			      			    });
		    				}
		    			});
					} else {
						swal("Pedido vazio, adicione caixas ao pedido!", "", "warning");
					}
				});
    });
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>
