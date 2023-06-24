<?php
include_once dirname(__FILE__) . '/core/utils/Input.php';
include_once dirname(__FILE__) . '/core/model/DbConnection.php';
include_once dirname(__FILE__) . '/core/control/BoxController.php';
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/core/config/Configuration.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");

use Docbox\control\ClientController;
use Docbox\control\DocumentTypeController;
use Docbox\model\DbConnection;
use Docbox\utils\Input;

$box_number = Input::getInt("n");
$client  = Input::getInt("c");

$box = NULL;
if($box_number > 0 && $client > 0) {
    $db = new DbConnection();
    $boxController = new BoxController($db);
    $box = $boxController->getBox($client, $box_number);
}

if($box == NULL) {
	header('HTTP/1.0 404 Not Found', true, 404);
	exit("Caixa sem registro");
}

$doctypeController = new DocumentTypeController($db);
$cliController = new ClientController($db);

$client = $cliController->getClient($client);
if($client == NULL) {
	header('HTTP/1.0 404 Not Found', true, 404);
	exit("Cliente sem registro");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<?php include('head.php'); ?>
<meta name="robots" content="noindex">
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
		<!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php">
                        <!-- Logo icon -->
                        <b>
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="assets/images/docbox_logo_icon.png" alt="homepage" class="dark-logo" width='34'/>
                            <!-- Light Logo icon -->
                            <img src="assets/images/docbox_logo_icon.png" alt="homepage" class="light-logo" width='34'/>
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span>
                         <!-- dark Logo text -->
                         <img src="assets/images/logo_light_text.png" alt="homepage" class="dark-logo" />
                         <!-- Light Logo text -->    
                         <img src="assets/images/logo_light_text.png" class="light-logo" alt="homepage" /></span> </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav mr-auto mt-md-0 ">
                        <!-- This is  -->
                        <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a> </li>
                        <h2 class='text-white'><?php if($client != NULL) echo $client->getName(); ?></h2>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
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
                        <h3 class="text-themecolor mb-0 mt-0">Caixa</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Documentos</a></li>
                            <li class="breadcrumb-item active">Listar conteúdo</li>
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
	                            <?php
	                            $colSize = "col-md-4";
		                        ?>
                                <h4 class="card-title">Caixa</h4>
                                <h6 class="card-subtitle"> Informações da caixa </h6>
                                <form id='form-register' class="mt-4" method='post' action='#' novalidate>
	                                <input id="req_token" name="req_token" type="hidden">

                                	<div class='row'>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_number">Nº da caixa</label>
			                                        <input id='box_number' name='box_number' type="number" class="form-control" placeholder="0" min='1' value="<?php echo $box->getNumber(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="box_department">Departamento</label>
			                                        <input id='box_department' name='box_department' type="text" class="form-control" value="<?php echo $box->getDepartment()->getName(); ?>" disabled>
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='<?= $colSize ?>'>
		                                    <div class="form-group">
		                                        <label for="doc_number">Situação</label>
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
												<th>Arquivo</th>
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
												<th>Ações</th>
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
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script type="text/javascript">
	$(function() {
		<?php if($hasDocs) { ?>
    	var tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"searching":false,
        	'paging':false,
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
				aoData.push(
					{"name" : "doc_box", "value" : <?php echo $box->getId(); ?>},
					{"name" : "c", "value" : <?= $client->getId() ?>}
				);
        	}, "aoColumnDefs" : [{
					"aTargets": [0, 2],
					"visible" : false
                }, {
            		"aTargets": [1],
            		orderable: false,
                    width: 40,
                    "mRender": function(d,t,f) {
                    	if(f[11] != "" && f[11] != null)
                        	return "<a target='blank' href='" + f[11] + "' class='btn btn-circle btn-primary'><i class='fa fa-file'></i></a>";
                    	return "";
                    }
                }, {
                	"aTargets": [4],
                    "mRender":function(d,t,f) {
                        if(parseInt(d) > 0) return d;
                        return "";
                    }
                }
            ]
        });
        <?php } else if($hasBooks) { ?>
        var tbDocuments = $('#data-table-basic').DataTable({
        	"serverSide": true,
        	"processing": true,
        	"aaSorting": [5, "asc"],
        	'paging':false,
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
        		aoData.push(
            		{"name" : "doc_box", "value" : <?php echo $box->getId(); ?>},
    				{"name" : "c", "value" : <?= $client->getId() ?>}
				);
        	}, 
        	"aoColumnDefs" : [{
                	"aTargets": [0,2],
                	"visible":false
            	}, {
            		"aTargets": [1],
            		orderable: false,
                    width: 120,
                    "mRender": function(d,t,f) {
                        var content = "";
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
        <?php } ?>
	});
    </script>
</body>

</html>