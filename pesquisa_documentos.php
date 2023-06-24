<?php
include_once (dirname ( __FILE__ ) . "/core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");
include_once (dirname ( __FILE__ ) . "/core/control/DocumentTypeController.php");
include_once (dirname ( __FILE__ ) . "/core/control/DepartmentController.php");
include_once (dirname ( __FILE__ ) . "/core/config/Configuration.php");

use Docbox\control\DepartmentController;
use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	header ( "Location: login.php" );
}

$db = new DbConnection ();
$doctypeController = new DocumentTypeController ( $db );
$depController = new DepartmentController($db);
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
                        <h3 class="text-themecolor mb-0 mt-0">Documentos</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Documentos</a></li>
                            <li class="breadcrumb-item active">Listar documentos</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                        <a href='cadastro_documento.php' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Novo</a>
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
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="doc_box">Nº da caixa</label>
			                                        <input id='doc_box' name='doc_box' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                    	<div class='col-md-2'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                        	<label class="control-label">Departamentos</label>
		                                            <select id='doc_department' name='doc_department' class="form-control">
		                                                <option value="" selected>Selecione...</option>
		                                                    <?php 
			                                                    $departments = $depController->getDepartments($user->getClient());
			                                                    $userDepartments = $user->getDepartments();
	
			                                                    foreach ($departments as $dep) {
			                                                    	if(in_array($dep->getId(), $userDepartments) || $user->isAdmin()) {
			                                                    		echo "<option value='" . $dep->getId() . "'>" . $dep->getName() . "</option>";
			                                                    	}
			                                                    }
		                                                    ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-2'>
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
		                                        <div class="controls">
	                                                <label class="control-label">Formato</label>
		                                            <select id='doc_format' name='doc_format' class="form-control">
		                                                <option value="" selected>Selecione...</option>
		                                                <option value="19">Documento</option>
		                                                <option value="89">Livro</option>
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
		                                        <label for="doc_volume">Volume</label>
		                                        <input id='doc_volume' name='doc_volume' type="number" class="form-control" placeholder="0" min='1'>
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
                                                <label class="control-label">Local</label>
                                                <select id='doc_location' name='doc_location' class="form-control custom-select">
                                                	<option value="" selected>TODOS</option>
                                                    <option value="1"><?= COMPANY_NAME ?></option>
                                                    <option value="2">No cliente</option>
                                                </select>
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
                                		<div class='col-md-4'>
		                                    <div class="form-group">
		                                        <label for="doc_company">Título</label>
		                                        <input id='doc_company' name='doc_company' type="text" class="typeahead form-control" autocomplete="off">
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Possui PDF</label>
                                                <select id='doc_has_file' name='doc_has_file' class="form-control custom-select">
                                                	<option value="" selected>TODOS</option>
                                                    <option value="1">SIM</option>
                                                    <option value="2">NÃO</option>
                                                </select>
                                            </div>
                                		</div>
                                	</div>
                                	<button type="reset" class="btn btn-default">Limpar</button>
									<button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i> Filtrar</button>
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
                                            </tr>
                                        </thead>
                                        <tfoot>
			                                <tr>
			                                    <th>#</th>
			                                    <th>Ações</th>
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
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script src="js/typeahead.js/typeahead.bundle.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
    	var refreshFilterDataLength = true;
    	var _iRecordsDisplay = 0;
    	var _iRecordsTotal = 0;

        $(document).ready(function() {
        	var tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [],
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
            	"sAjaxSource": "./core/data-tables/TableDocBooks.php",
            	"sServerMethod" : "POST",
            	"fnServerParams" : function(aoData) {
    				aoData.push (
    					{"name": "box_number", "value" : $("#doc_box").val()},
    					{"name": "doc_year", "value" : $("#doc_year").val()},
    					{"name": "doc_number", "value" : $("#doc_number").val()},
    					{"name": "doc_volume", "value" : $("#doc_volume").val()},
    					{"name": "doc_letter", "value" : $("#doc_letter").val()},
    					{"name": "doc_type", "value" : $("#doc_type").val()},
    					{"name": "doc_department", "value" : $("#doc_department").val()},
    					{"name": "doc_format", "value" : $("#doc_format").val()},
    					{"name": "doc_location", "value" : $("#doc_location").val()},
    					{"name": "doc_date_from", "value" : $("#doc_date_from").val()},
    					{"name": "doc_date_to", "value" : $("#doc_date_to").val()},
    					{"name": "doc_title", "value" : $("#doc_company").val()},
    					{"name": "doc_has_file", "value" : $("#doc_has_file").val()},
        				{"name" : "refresh_data_length", "value" : refreshFilterDataLength == true ? 1 : 0},
        				{"name":"_iRecordsDisplay", "value": _iRecordsDisplay},
        				{"name" : "_iRecordsTotal", "value" : _iRecordsTotal}
    				);
            	},
            	"aoColumnDefs" : [{
                    	"aTargets": [0],
                    	"visible":false
                	}, {
                		"aTargets": [1],
                		orderable: false,
                        width: 128,
                        "mRender": function(d,t,f) {
                            var content = "";
	                        <?php if($user->isAdmin()) { ?>
		                        if(f[3] == 0) {
		                        	content += "<a target='_blank' href='editar_documento.php?doc=" + f[0] + "' class='btn btn-circle btn-success'><i class='fa fa-edit'></i></a>";
		                        } else {
                                	content += "<a href='editar_livro.php?book=" + f[0] + "' class='btn btn-circle btn-success'><i class='fa fa-edit'></i></a>";
		                        }
                            <?php }?>
                            content += ((f[16] == null || f[16] == 'null' || f[16] == undefined || f[16] == "") ? "" : "<a target='blank' href='" + f[16] + "' class='btn btn-circle btn-primary'><i class='fa fa-eye'></i></a>");
                            return content;
                        }
                    }, {
                        "aTargets": [2],
                        width: 72,
                        "mRender": function(d,t,f) {
                            return "<a href='visualizar_caixa.php?box=" + f[1] + "'>" + d + "</a>";
                        }
                    }, {
                    	"aTargets" : [3],
                    	"mRender": function(d,t,f) {
                        	return d == 0 ? "Documento" : 'Livro';
                    	}
                    },{
                    	"aTargets": [13],
                    	"mRender":function(d,t,f) {
                           	if(d > 0) {
                              	return "<a target='_blank' href='visualizar_pedido_caixas.php?r=" + d + "'>Em pedido - Dep. " + f[17] + " (" + f[18] + ")</a>";
                            } else if(f[14] > 0) {
                            	return "<a target='_blank' href='visualizar_pedido_documentos.php?r=" + f[14] + "'>Em pedido - Dep. " + f[17] + " (" + f[18] + ")</a>";
                            } else {
                                return "<?= COMPANY_NAME ?>";
                            }
                       	}
                    }
                ],
                "drawCallback" : function (settings) {
                	_iRecordsTotal = settings._iRecordsTotal;
                	_iRecordsDisplay = settings._iRecordsDisplay;
                	refreshFilterDataLength = false;
                }
            });
        	$("#form-filter-document").submit(function(e) {
            	refreshFilterDataLength = true;

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