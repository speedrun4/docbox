<?php
	include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
	include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
	include_once (dirname(__FILE__) . "/core/config/Configuration.php");

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
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Livros</a></li>
                            <li class="breadcrumb-item active">Listar livros</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                        <a href='cadastro_livro.php' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Novo</a>
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
			                                        <label for="liv_box">Nº da caixa</label>
			                                        <input id='liv_box' name='liv_box' type="number" class="form-control" placeholder="0" min='1' value=''>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
	                                		<div class="form-group">
		                                        <div class="controls">
	                                                <label class="control-label">Tipo de documento</label>
		                                            <select id='liv_type' name='liv_type' class="form-control">
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
		                                    	<div class='controls'>
			                                        <label for="liv_year">Ano</label>
			                                        <input id='liv_year' name='boo_year' type="number" class="form-control" placeholder="" min='1900' max='9999'>
		                                    	</div>
		                                    </div>
                                		</div>
                                    	<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
    		                                        <label for="liv_num">Nº documento</label>
    		                                        <input id='liv_num' name='liv_num' type="number" class="form-control" placeholder="0" min='1'>
		                                        </div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                        <label for="liv_volume">Volume</label>
		                                        <input id='liv_volume' name='liv_volume' type="number" class="form-control" placeholder="0" min='1'>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
                                			<div class="form-group">
                                                <label class="control-label">Local</label>
                                                <select id='liv_location' name='liv_location' class="form-control">
                                                	<option value="" selected>TODOS</option>
                                                    <option value="1"><?= COMPANY_NAME ?></option>
                                                    <option value="2">No cliente</option>
                                                </select>
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
        $(document).ready(function() {
        	var tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [4, "asc"],
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
    				aoData.push (
    					{"name": "liv_box", "value" : $("#liv_box").val()},
    					{"name": "liv_type", "value" : $("#liv_type").val()},
    					{"name": "liv_year", "value" : $("#liv_year").val()},
    					{"name": "liv_num", "value" : $("#liv_num").val()},
    					{"name": "liv_volume", "value" : $("#liv_volume").val()},
    					{"name": "liv_location", "value" : $("#liv_location").val()});
            	}, 
            	"aoColumnDefs" : [{
                    	"aTargets": [0],
                    	"visible":false
                	}, {
                		"aTargets": [1],
                		orderable: false,
                        width: 120,
                        "mRender": function(d,t,f) {
	                        // Botão de edição
                        	return "<a href='editar_livro.php?book=" + f[0] + "' class='btn btn-circle btn-success'><i class='fa fa-edit'></i></a>";
                        }
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
                ]
            });
        	$("#form-filter-document").submit(function(e) {
            	tbDocuments.ajax.reload();
                e.preventDefault();
            });
        });
    </script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
</body>

</html>
