<?php
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;

include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/BoxController.php");
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0 || !$user->isAdmin()) {
    header("Location: login.php");
}

$db = new DbConnection();
$boxController = new BoxController($db);

$box_id = getReqParam("box", "int", "get");
$box = $boxController->getBoxById($box_id);

if($box == NULL || $box->getClient() != $user->getClient()) {
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <?php include('head.php'); ?>
    <style type="text/css">
    .custom-control-label::before, .custom-control-label::after {
        width: 1.5rem;
        height: 1.5rem;
    }
    #numSelecteds {
        /*font-weight: bold;*/
        color: green;
        line-height: 32px;
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Caixas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Caixas</a></li>
                            <li class="breadcrumb-item"><a href="listar_caixas.php">Listar Caixas</a></li>
                            <li class="breadcrumb-item active">Visualizar caixa</li>
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
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
	                            <?php
	                            $colSize = "col-md-12";//$user->isAdmin() ? "col-md-2" : "col-md-4";
		                        ?>
                                <h4 class="card-title">Caixa de origem</h4>
                                <h6 class="card-subtitle"> Informações da caixa </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="box_id" name="box_id" type="hidden" value='<?= $box_id ?>'>
                                	<div class='row'>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label>Nº da caixa</label>
			                                        <input type="number" class="form-control" placeholder="0" min='1' value="<?php echo $box->getNumber(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label>Departamento</label>
			                                        <input type="text" class="form-control" value="<?php echo $box->getDepartment()->getName(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                        <label>Situação</label>
		                                        <input type="text" class="form-control" value="<?php
			                                    if($box->getRequest() == NULL || $box->getRequest()->getStatus() == NULL || $box->getRequest()->getStatus() == 2 || $box->getRequest()->getStatus() == 5) {
			                                        echo COMPANY_NAME;
			                                    } else {
			                                        echo "Em pedido";
			                                    }
			                                    ?>" disabled>
		                                    </div>
                                		</div>
                                	</div>
                                	<button id='btTransferDocs' type='button' class='btn btn-success btn-icon float-right'><i class='fa fa-arrow-right'></i> Transferir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Caixa destino</h4>
                                <h6 class="card-subtitle"> Informações da caixa </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="new_box_id" name="new_box_id" type="hidden" value=''>
                                	<div class='row'>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_number">Nº da caixa</label>
			                                        <input id='box_number' name='box_number' type="number" class="form-control" placeholder="0" min='1' value="">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_department">Departamento</label>
			                                        <input id='box_department' name='box_department' type="text" class="form-control" value="" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                        <label for="box_situation">Situação</label>
		                                        <input id='box_situation' type="text" class="form-control" value="" disabled>
		                                    </div>
                                		</div>
                                	</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
				<?php if($hasDocs = $boxController->hasDocs($box)) { ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Documentos</h4>
								<h6 class="card-subtitle">Lista dos documentos armazenados na caixa</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
											</tr>
										</thead>
										<tfoot>
											<tr>
												<th>#</th>
												<th>#</th>
												<th>Caixa</th>
												<th>Tipo</th>
												<th>Número</th>
												<th>Ano</th>
												<th>Letra</th>
												<th>Volume</th>
												<th>Título</th>
												<th>Data</th>
											</tr>
										</tfoot>
										<tbody>
										</tbody>
									</table>
								</div>
								<button id='btCheckPage' class='btn btn-primary'><i class='fa fa-tasks'></i> Marcar página</button>
								<button id='btUncheckPage' class='btn btn-secondary'><i class='fa fa-list'></i> Desmarcar página</button>
								<p style="display: inline-block; margin-left: 10px"><span id='numSelecteds'>0</span> documentos selecionados</p>
							</div>
						</div>
					</div>
				</div>
				<?php } else if($hasBooks = $boxController->hasBooks($box)) { ?>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Livros</h4>
								<h6 class="card-subtitle">Lista dos livros armazenados na caixa</h6>
								<div class="table-responsive m-t-40">
									<table id="data-table-basic" class="table table-bordered table-striped">
										<thead>
                                            <tr>
                                                <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Caixa</th>
			                                    <th>Tipo</th>
			                                    <th>Ano</th>
			                                    <th>N&ordm; inicial</th>
			                                    <th>N&ordm; final</th>
			                                    <th>Volume</th>
			                                    <th>Local</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
			                                <tr>
			                                    <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Caixa</th>
			                                    <th>Tipo</th>
			                                    <th>Ano</th>
			                                    <th>N&ordm; inicial</th>
			                                    <th>N&ordm; final</th>
			                                    <th>Volume</th>
			                                    <th>Local</th>
			                                </tr>
		                                </tfoot>
										<tbody></tbody>
									</table>
								</div>
								<button id='btCheckPage' class='btn btn-primary'><i class='fa fa-tasks'></i> Marcar página</button>
								<button id='btUncheckPage' class='btn btn-secondary'><i class='fa fa-list'></i> Desmarcar página</button>
								<p style="display: inline-block; margin-left: 10px"><span id='numSelecteds'>0</span> livros selecionados</p>
							</div>
						</div>
					</div>
				</div>
				<?php } else { ?>
				<p>Caixa vazia...</p>
				<?php } ?>
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
    <?php include("modal_substitute_box.php"); ?>
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
    <script type="text/javascript">
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    function showTbCheckbox(d,t,f) {
        return '<div class="custom-control custom-checkbox mr-sm-2">' +
        			'<input type="checkbox" class="custom-control-input" id="select-box-' + f[0] + '" name="select-box-' + f[0] + '" data-id="' + f[0] + '" value="on">' +
    				'<label class="custom-control-check-label custom-control-label" for="select-box-' + f[0] + '"></label>' +
    			'</div>';
    }
    function dataTableDrawCallback(settings) {
    	$("[id^='select-box']").each(function(i, v) {
    		$(v).change(function() {
            	if($(this).prop("checked")) {
        			selectedDocuments.push($(this).data().id);
        			$("#numSelecteds").text(selectedDocuments.length);
            	} else {
            		selectedDocuments.splice(selectedDocuments.indexOf($(this).data().id), 1);
            		$("#numSelecteds").text(selectedDocuments.length);
            	}
        	});

    		selectedDocuments.forEach(function (value) {
            	if($(v).data().id == value) {
            		$(v).prop('checked', true);
            	} 
        	});
    	});
    }
    var selectedDocuments = [];
    $(function() {
        <?php if($hasDocs) { ?>
    	var tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"searching":false,
        	"aaSorting": [4, "asc"],
        	"language" : {
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
        	},
        	"sAjaxSource" : "./core/data-tables/TableDocuments.php",
        	"sServerMethod" : "POST",
        	"fnServerParams" : function(aoData) {
				aoData.push({"name" : "doc_box", "value" : <?php echo $box->getId(); ?>});
        	}, "aoColumnDefs" : [{
					"aTargets": [0],
					"visible" : false
                }, {
            		"aTargets" : [1],
            		orderable: false,
                    width: 48,
                    'className': 'dt-body-center',
                    "mRender": showTbCheckbox
                }, {
                	"aTargets": [4],
                    "mRender" : function(d,t,f) {
                        if(parseInt(d) > 0) return d;
                        return "";
                    }
                }
            ], "drawCallback": dataTableDrawCallback
        });
        <?php } else if($hasBooks) { ?>
        var tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"aaSorting": [5, "asc"],
        	"language" : {
        	    "sEmptyTable": "Nenhum documento encontrado",
        	    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ documentos",
        	    "sInfoEmpty": "Mostrando 0 até 0 de 0 documentos",
        	    "sInfoFiltered": "(Filtrados de _MAX_ documentos)",
        	    "sInfoPostFix": "",
        	    "sInfoThousands": ".",
        	    "sLengthMenu": "_MENU_ resultados por página",
        	    "sLoadingRecords": "Carregando...",
        	    "sProcessing": "Processando...",
        	    "sZeroRecords": "Nenhum documento encontrado",
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
        	"sAjaxSource": "./core/data-tables/TableBooks.php",
        	"sServerMethod" : "POST",
        	"fnServerParams" : function(aoData) {
        		aoData.push({"name" : "doc_box", "value" : <?php echo $box->getId(); ?>});
        	}, 
        	"aoColumnDefs" : [{
                	"aTargets": [0],
                	"visible":false
            	}, {
            		"aTargets": [1],
            		orderable: false,
                    width: 64,
                    "mRender": showTbCheckbox
                }, {
                    "aTargets": [2],
                    width: 72,
                    "mRender": function(d,t,f) {
                        return "<a href='visualizar_caixa.php?box=" + f[1] + "'>" + d + "</a>";
                    }
                }, {
                	"aTargets" : [3],
                	//visible:false
                }, {
                	"aTargets": [8],
                	"mRender":function(d,t,f) {
                    	if(d > 0) {
                        	return "<a href='visualizar_pedido_caixas.php?r=" + d + "'>Em pedido</a>";
                        } else {
                            return "<?= COMPANY_NAME ?>";
                        }
                    }
                }
            ], "drawCallback": dataTableDrawCallback
        });
        <?php } ?>

        $("#btTransferDocs").click(function() {
            if(selectedDocuments.length < 1) {
                swal("Atenção", "Nenhum documento foi selecionado!", "warning");
            } else {
            	swal({
                    type: 'question',
                    title: "Confirma a transferência dos documentos?",
                    cancelButtonText: 'Não',
                    confirmButtonText: 'Sim',
                    showCancelButton: true,
                }).then((result) => {
                    if(result.value) {
                        $.post("./core/actions/transferDocuments.php", {box_from:<?= $box->getId() ?>, box_to:$("#new_box_id").val(), documents:selectedDocuments}, 
                            function(res) {
                                if(res.ok == true) {
                                    swal("Transferência realizada com sucesso!", "", "success").then(
                                         ()=> {
                                         	window.location.href = "listar_caixas.php"
                                         });
                                } else {
                                    swal("Atenção", res.message, res.type);
                                }
                        	},
                        "json").fail(function() {
                            swal("Erro", "Ocorreu um erro ao realizar a operação. Verifique sua conexão com a internet e tente novamente mais tarde.", "error");
                        });
                    }
                });
            }
        });

        $("#btCheckPage").click(function() {
            $("[id^='select-box']").each(function(i, v) {
            	if(selectedDocuments.indexOf($(v).data().id) === -1) {
                	selectedDocuments.push($(v).data().id);
                	$(v).prop('checked', true);
                	$("#numSelecteds").text(selectedDocuments.length);
            	}
            });
        });

        $("#btUncheckPage").click(function() {
            $("[id^='select-box']").each(function(i, v) {
                var i = 0;
                for(;i<selectedDocuments.length;) {
                    if(selectedDocuments[i] == $(v).data().id) {
                    	selectedDocuments.splice(i, 1);
                    	$(v).prop('checked', false);
                    	$("#numSelecteds").text(selectedDocuments.length);
                    	continue;
                    }
                    i++;
                }
            });
        });

        $("#box_number").change(() => {
        	$("#new_box_id").val('');
        	$("#box_department").val("");
        	$("#box_situation").val("");
            $.get("./core/actions/getBox.php", {box_number:$("#box_number").val()}, function(res) {
                if(res.ok) {
                	$("#new_box_id").val(res.box.id);
                    $("#box_department").val(res.box.department);
                    if(res.box.request > 0) {
                        $("#box_situation").val("Em pedido");
                    } else {
                    	$("#box_situation").val("<?= COMPANY_NAME ?>");
                    }
                } else {
                	swal("Atenção", res.message, res.type);
                }
                }, 'json').fail(function() {
                	swal("Erro ao realizar a operação", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
                });
        });
    });
    </script>
</body>

</html>