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
                        <h3 class="text-themecolor m-b-0 m-t-0">Pedidos</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Pedidos</a></li>
                            <li class="breadcrumb-item active">Cadastrar Pedidos</li>
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
		                                    	<div class='controls'>
			                                        <label for="box_number">Nº da caixa</label>
			                                        <input id='box_number' name='box_number' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
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
								<h4 class="card-title">Documentos</h4>
								<h6 class="card-subtitle">Clique no botão verde para adicionar caixas ao pedido</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Formato</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>N&ordm; inicial</th>
												<th>N&ordm; final</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
												<th>Local</th>
												<th>Ações</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Formato</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>N&ordm; inicial</th>
												<th>N&ordm; final</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
												<th>Local</th>
												<th>Ações</th>
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
								<h4 class="card-title">Pedido</h4>
								<h6 class="card-subtitle">Lista de caixas do pedido</h6>
								<div class="table-responsive m-t-40">
									<table id="tbRequestBoxes" class="table table-bordered table-striped">
										<thead>
        									<tr>
            									<th>Ações</th>
            									<th>#</th><!-- Id da caixa -->
            									<th>Caixa N&ordm;</th>
        									</tr>
        								</thead>
        								<tfoot>
        									<tr>
            									<th>Ações</th>
            									<th>#</th><!-- Id da caixa -->
            									<th>Caixa N&ordm;</th>
        									</tr>
        								</tfoot>
									</table>
								</div>
								<div class='row'>
									<div class='col-md-12'>
										<button id='btConfirmRequest' class='btn btn-primary m-t-10 float-right'>Finalizar pedido</button>
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
    $(function() {
    	blockFormEnter('#form-register,#form-register-box');

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
            tbBoxes = $("#tbRequestBoxes").DataTable({
            	"language" : _language,
            	"lengthChange" : false,
            	"searching" : false,
            	"aaSorting": [2, "asc"],
            	"aoColumnDefs" : [{
                	"aTargets": [1],
                	"visible":false
            	}, {
            		"aTargets": [0],
            		"width" : 80
                	}]
            });
        	tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [4, "asc"],
            	"language" : _language,
            	"sAjaxSource" : "./core/data-tables/TableDocBooks.php",
            	"sServerMethod" : "POST",
                "fnServerParams": function(aoData) {
                	var documentBoxes = [];
    				for(var i = 0; i < tbBoxes.rows().data().length; i++) {
    					documentBoxes.push(tbBoxes.row(i).data()[1]);
    				}
    				aoData.push(
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
    					{"name": "box_marks", 'value': documentBoxes},
    					{"name": "refresh_data_length", "value": 1}
    					);
            	},
            	"drawCallback" : function(settings) {
                	$(".btAddBox").click(function() {
                    	var box = $(this).data().box;
                    	if(!isInRequestList(box)) {
                        	addToRequestList($(this).data().box, $(this).data().number);
                        	tbDocuments.ajax.reload();
                       	}
                    });
                },
            	"aoColumnDefs" : [{
                    	"aTargets": [0, 1, 8],
                    	"visible":false
                	}, {
                    	"aTargets" : [3],
                    	"mRender": function(d,t,f) {
                        	return d == 0 ? "Documento" : 'Livro';
                    	}
                    }, {
						"aTargets" : [10],
						"mRender":function(d,t,f) {
							return (d != undefined) ? d.toUpperCase() : "";
						}
					}, {
                   	"aTargets": [13],
                   	"mRender":function(d,t,f) {
                       	if(d > 0) {
                          	return "<a href='visualizar_pedido_caixas.php?r=" + d + "'>Em pedido</a>";
                        } else if(f[15] == true) {// bloqueada
                        	return "Bloqueada por pedido";
                        } else {
                            return '<?= COMPANY_NAME ?>';
                        }
                   }
                }, {
                    "aTargets" : [14],
                    "width": 32,
                    "orderable":false,
                    "mRender" : function(d,t,f) {
                        if(f[13] > 0 || f[15] == true) {// Em pedido ou bloqueada
                            return "";
                        }
                        var disabled = isInRequestList(f[1]) ? "disabled" : "";
                    	return "<button class='btAddBox btn btn-circle btn-success waves-effect' data-box='" + f[1] + "' data-number='" + f[2] + "' " + disabled +"><i class='fa fa-plus'></i></button>";
                    }
                }
        	]
        });
        $("#form-filter-document").submit(function(e) {
           	tbDocuments.ajax.reload();
            e.preventDefault();
            $('html, body').animate({
        		scrollTop: $('#data-table-basic').offset().top - 128
        	}, 1000);
        });
        $("#btConfirmRequest").click(function() {
			if(tbBoxes.rows().data().length > 0) {
				var documentBoxes = [];
				for(var i = 0; i < tbBoxes.rows().data().length; i++) {
					documentBoxes.push(tbBoxes.row(i).data()[1]);
				}
				swal({
        			title: 'Deseja realmente finalizar o pedido?',
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
	    				$.post("./core/actions/registerRequest.php", {"boxes" : documentBoxes}, function(data) {
	  			        	if(data.ok) {
	  			        		window.location.href = "visualizar_pedido_caixas.php?r=" + data.req_id;
	  			        	} else {
								swal(data.error, "", data.type);
	  			        	}
	  			        }, 'json').fail(function(xhr, status, error){
	  			        	swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
	      			    });
    				}
    			});
			} else {
				swal("Pedido vazio, adicione caixas ao pedido!", "", "warning");
			}
		});
    });
    function isInRequestList(box) {
    	return $("#tbRequestBoxes").DataTable().rows().data().filter(function(value) {
    		return (parseInt(value[1]) == parseInt(box));
    	}).length > 0;
    }
    function addToRequestList(box, number) {
    	$("#tbRequestBoxes").DataTable().row.add([
			"<button class='btRemoveBox btn btn-circle btn-danger' data-box='" + box + "'><i class='fa fa-trash-alt'></i></button>",
			box,
			number
    	]).draw();
    	$(".btRemoveBox").off();
    	$(".btRemoveBox").click(function() {
            $("#tbRequestBoxes").DataTable().row( $(this).parents('tr'))
            .remove()
            .draw();
            tbDocuments.ajax.reload();
            $("#numSelectedBoxes").html(tbBoxes.rows().data().length);
        });
    	$("#numSelectedBoxes").html(tbBoxes.rows().data().length);
    }
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>