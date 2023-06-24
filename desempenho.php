<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/StatisticsController.php");
include_once (dirname(__FILE__) . "/core/utils/Utils.php");

use Docbox\control\ClientController;
use Docbox\control\StatisticsController;
use function Docbox\control\getUserLogged;
use function Docbox\control\setUserClient;
use Docbox\model\DbConnection;
use function Docbox\utils\getReqParam;

$user = getUserLogged();

if($user == NULL) {
	header("Location: login.php");
}

$db = new DbConnection();
$staController = new StatisticsController($db);
$cliController = new ClientController($db);

// Se existe somente um cliente já abre com ele escolhido
$clients = $cliController->getClients();
if(count($clients) == 1 && $user->getClient() < 1) {
	setUserClient($clients[0]->id);
	header("Location: index.php");
}
$chartYear = getReqParam('y', 'int', 'get');
if($chartYear == 0) $chartYear = date('Y');
?><!DOCTYPE html>
<html lang="pt">

<head>
    <?php include('head.php'); ?>
    <link href="assets/plugins/datatables/media/css/dataTables.foundation.css" rel="stylesheet">
	<style type="text/css">
	.ct-series-a .ct-bar {
		/* Colour of your bars */
		stroke: #28AA55;
		/* The width of your bars */
/* 		stroke-width: 20px; */
		/* Yes! Dashed bars! */
		/*stroke-dasharray: 20px;*/
		/* Maybe you like round corners on your bars? */
		/*stroke-linecap: round;*/
	}
	.ct-series-b .ct-bar {
		/* Colour of your bars */
		stroke: red;
		/* The width of your bars */
/* 		stroke-width: 20px; */
		/* Yes! Dashed bars! */
		/*stroke-dasharray: 20px;*/
		/* Maybe you like round corners on your bars? */
		/*stroke-linecap: round;*/
	}
	.ct-label {
	   color: black    ;
	}
	</style>
</head>

<body class="fix-header fix-sidebar card-no-border">
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
        <?php include('header.php'); ?>
        <?php include('aside_menu.php'); ?>
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
                        <h3 class="text-themecolor mb-0 mt-0">Início</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="listar_usuarios.php">Usuários</a></li>
                            <li class="breadcrumb-item active">Desempenho</li>
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
                                <h4 class="card-title">Filtros</h4>
                                <h6 class="card-subtitle">Utilize os campos abaixo para filtrar os resultados do gráfico.</h6>
                                <form id='form-filter-chart' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-4'>
                                			<div class="form-group">
                                                <label class="control-label">Ações</label>
                                                <select id='chart_action' name='chart_action' class="form-control custom-select">
                                                	<option value="" selected>TODOS</option>
                                                    <option value="I">Cadastro</option>
                                                    <option value="U">Alteração</option>
                                                    <option value="D">Exclusão</option>
                                                </select>
                                            </div>
                                		</div>
                                		<div class='col-md-4'>
		                                    <div class="form-group">
		                                        <label for="chart_from">Data inicial</label>
		                                        <input id='chart_from' name='chart_from' type="date" class="form-control">
		                                    </div>
                                		</div>
                                		<div class='col-md-4'>
		                                    <div class="form-group">
		                                        <label for="chart_to">Data final</label>
		                                        <input id='chart_to' name='chart_to' type="date" class="form-control">
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
                <!-- Row -->
                <div class="row">
                    <!-- Column -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="d-flex flex-wrap">
                                            <div>
                                                <h3>Gráfico de Atividades</h3>
                                                <!--h6 class="card-subtitle">Ano <?= $chartYear ?></h6> </div>
                                            <div class="ml-auto ">
                                                <ul class="list-inline">
                                                    <li>
                                                        <h6 class="text-muted"><i class="fa fa-circle mr-1 text-success"></i>Pedidos realizados</h6> </li>
                                                    <li>
                                                        <h6 class="text-muted"><i class="fa fa-circle mr-1 text-danger"></i>Pedidos cancelados</h6> </li>
                                                </ul>
                                            </div-->
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="total-revenue4" style="height: 350px;"></div>
                                    </div>
                                    <!--div class="col-lg-3 col-md-6 mb-4 mt-3 text-center">
                                        <h1 class="mb-0 font-light">$54578</h1>
                                        <h6 class="text-muted">Total Revenue</h6></div>
                                    <div class="col-lg-3 col-md-6 mb-4 mt-3 text-center">
                                        <h1 class="mb-0 font-light">$43451</h1>
                                        <h6 class="text-muted">Online Revenue</h6></div>
                                    <div class="col-lg-3 col-md-6 mb-4 mt-3 text-center">
                                        <h1 class="mb-0 font-light">$44578</h1>
                                        <h6 class="text-muted">Product A</h6></div>
                                    <div class="col-lg-3 col-md-6 mb-4 mt-3 text-center">
                                        <h1 class="mb-0 font-light">$12578</h1>
                                        <h6 class="text-muted">Product B</h6></div-->
                                </div>
                                <div class="col-6">
                                    <div class="d-flex flex-wrap">
                                        <div>
                                            <h3>Cadastro de caixas</h3>
                                    	</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="box-chart" style="height: 350px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
            </div>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        	<h4 class="card-title">Média de cadastros diários</h4>
                        	<h6 class="card-subtitle">Exibe a média de cadastros diários de cada usuário</h6>
                        	<div class="table-responsive m-t-40">
                                <table id="table-average" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
		                                    <th>Usuário</th>
		                                    <th>Cadastros</th>
		                                    <th>Dias trabalhados</th>
		                                    <th>Média</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach($staController->getAverageUserDocsByDay() as $row) {?>
                                        <tr>
                                        	<td><?= $row[0] ?></td>
                                        	<td><?= $row[1] ?></td>
                                        	<td><?= $row[2] ?></td>
                                        	<td><?= $row[3] ?></td>
                                        </tr>
                                    <?php }
                                    ?>
                                    </tbody>
                                    <tfoot>
		                                <tr>
		                                    <th>Usuário</th>
		                                    <th>Cadastros</th>
		                                    <th>Dias trabalhados</th>
		                                    <th>Média</th>
		                                </tr>
	                                </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <?php include('right_sidebar.php'); ?>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
        <?php include('footer.php'); ?>
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
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- chartist chart -->
    <script src="assets/plugins/chartist-js/dist/chartist.min.js"></script>
    <script src="assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js"></script>
    <!-- Chart JS -->
    <script src="assets/plugins/echarts/echarts-all.js"></script>
    <script src="assets/plugins/toast-master/js/jquery.toast.js"></script>
    <!-- Chart JS -->
    <script src="js/dashboard1.js"></script>
    <script src="js/toastr.js"></script>
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- This is data table -->
    <script src="assets/plugins/datatables/datatables.min.js"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <script type="text/javascript">
    $(function(){
    	// ============================================================== 
        // Total revenue chart
        // ============================================================== 
        new Chartist.Bar('.total-revenue4', <?php echo json_encode($staController->getPerformanceChart("", "", "")); ?>, {
            reverseData: true,
            distributeSeries: true,
            horizontalBars: true,
        	seriesBarDistance: 10,
            low: 0,
            showArea: true,
			fullWidth: true,
            plugins: [Chartist.plugins.tooltip()], // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
			axisY: { offset: 80 },
          	axisX: {
          		onlyInteger: true,
        	    // offset: 60,
        	    labelInterpolationFnc: function(value) {
        	      return value + ''
        	    },
        	    scaleMinSpace: 25
        	}
        });

        new Chartist.Bar('.box-chart', <?php echo json_encode($staController->getBoxRegisteredPerformance("", "")); ?>, {
            reverseData: true,
            distributeSeries: true,
        	seriesBarDistance: 10,
            low: 0,
            showArea: true,
			fullWidth: true,
            plugins: [Chartist.plugins.tooltip()], // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
			axisY: { offset: 80 },
          	axisX: {
          		onlyInteger: true,
        	    // offset: 60,
        	    labelInterpolationFnc: function(value) {
        	      return value + ''
        	    },
        	    scaleMinSpace: 25
        	}
        });

        $("#form-filter-chart").submit(function(e){
            $.post("./core/actions/getPerformanceChart.php", $("#form-filter-chart").serialize(), function(data) {
            	new Chartist.Bar('.total-revenue4', data, {
                    reverseData: true,
                    distributeSeries: true,
                    horizontalBars: true,
                	seriesBarDistance: 10,
                	low: 0,
                    showArea: true,
        			fullWidth: true,
                    plugins: [Chartist.plugins.tooltip()], // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
        			axisY: { offset: 80 },
                  	axisX: {
                  		onlyInteger: true,
                	    // offset: 60,
                	    labelInterpolationFnc: function(value) {
                	      return value + ''
                	    },
                	    scaleMinSpace: 25
                	}
                });
            }, 'json');
            $.post("./core/actions/getBoxRegisteredPerformance.php", $("#form-filter-chart").serialize(), function(data) {
            	new Chartist.Bar('.box-chart', data, {
                    reverseData: true,
                    distributeSeries: true,
                	seriesBarDistance: 10,
                	low: 0,
                    showArea: true,
        			fullWidth: true,
                    plugins: [Chartist.plugins.tooltip()], // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
        			axisY: { offset: 80 },
                  	axisX: {
                  		onlyInteger: true,
                	    // offset: 60,
                	    labelInterpolationFnc: function(value) {
                	      return value + ''
                	    },
                	    scaleMinSpace: 25
                	}
                });
            }, 'json');
            e.preventDefault();
        });

        $("#table-average").DataTable({
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
        });
    });
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>