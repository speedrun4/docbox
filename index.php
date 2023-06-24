<?php
include_once (dirname(__FILE__) . "/core/utils/Utils.php");
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/StatisticsController.php");

use DocBox\model\RequestStatus;
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
// TODO Exibir número de pedidos de devolução na página inicial
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
                        <h3 class="text-themecolor mb-0 mt-0">Dashboard</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
<!--                         <a href='cadastro_pedido.php' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Criar</a> -->
                        <div class="dropdown float-right mr-2 hidden-sm-down">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <?php echo $chartYear; ?> </button>
							<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<?php
								$years = $staController->getYearsContainsRequests();

								foreach($years as $year) {
									$active = $year == $chartYear ? "active" : "";
									echo "<a class='dropdown-item $active' href='index.php?y=$year'>$year</a>";
								}
							?>
							</div>
						</div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <!-- Column -->
                    <div class="col-md-6 col-lg-3 col-xlg-3" onclick='redirectToRequests(1)' style="cursor: pointer;">
                        <div class="card card-inverse card-info">
                            <div class="box bg-info text-center">
                                <h1 class="font-light text-white"><?php echo $staController->getTotalOpenedRequests($user->getClient()); ?></h1>
                                <h6 class="text-white">Pedidos Abertos</h6>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="col-md-6 col-lg-3 col-xlg-3" onclick='redirectToRequests(5)' style="cursor: pointer;">
                        <div class="card card-primary card-inverse">
                            <div class="box text-center">
                                <h1 class="font-light text-white"><?php echo $staController->getTotalOpenedDevolutions($user->getClient()); ?></h1>
                                <h6 class="text-white">Devoluções abertas</h6>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="col-md-6 col-lg-3 col-xlg-3" onclick='redirectToRequests(4)' style="cursor: pointer;">
                        <div class="card card-inverse card-success">
                            <div class="box text-center">
                                <h1 class="font-light text-white"><?php echo $staController->getTotalAttendedRequests($user->getClient()); ?></h1>
                                <h6 class="text-white">Pedidos Atendidos</h6>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="col-md-6 col-lg-3 col-xlg-3" onclick='redirectToRequests(777)' style="cursor: pointer;">
                        <div class="card card-inverse card-warning">
                            <div class="box text-center">
                                <h1 class="font-light text-white"><?php echo $staController->getTotalRequestsPerMonth($user->getClient(), date('n')); ?></h1>
                                <h6 class="text-white">Pedidos no mês</h6>
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
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap">
                                            <div>
                                                <h3>Pedidos por mês</h3>
                                                <h6 class="card-subtitle">Ano <?= $chartYear ?></h6> </div>
                                            <div class="ml-auto ">
                                                <ul class="list-inline">
                                                    <li>
                                                        <h6 class="text-muted"><i class="fa fa-circle mr-1 text-success"></i>Pedidos realizados</h6> </li>
                                                    <li>
                                                        <h6 class="text-muted"><i class="fa fa-circle mr-1 text-danger"></i>Pedidos cancelados</h6> </li>
                                                </ul>
                                            </div>
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
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row -->
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <?php include('right_sidebar.php'); ?>
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
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
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <script type="text/javascript">
    $(function(){
    	// ============================================================== 
        // Total revenue chart
        // ============================================================== 
        new Chartist.Bar('.total-revenue4', {
        	labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            series: [
            	<?php echo json_encode($staController->getTotalRequestsPerYear($user->getClient(), NULL, $chartYear)); ?>,// Realizados
            	<?php echo json_encode($staController->getTotalRequestsPerYear($user->getClient(), RequestStatus::CANCELED, $chartYear)); ?>// Cancelados
          ]
        }, {
        	seriesBarDistance: 10,
            low: 0,
            showArea: true,
			fullWidth: true,
            plugins: [Chartist.plugins.tooltip()], // As this is axis specific we need to tell Chartist to use whole numbers only on the concerned axis
			axisX: { offset: 60 },
          	axisY: {
          		onlyInteger: true,
        	    offset: 80,
        	    labelInterpolationFnc: function(value) {
        	      return value + ''
        	    },
        	    scaleMinSpace: 15
        	  }
        });
        
    	<?php if($user->isAdmin() && $user->getClient() == NULL) { ?>
        swal({
            title: "Por favor selecione o cliente",
            html:"<hr/><p></p><?php 
                $clients = $cliController->getClients();
                    foreach($clients as $client) {
                    	echo "<p><a class='btn btn-primary text-white' onClick='changeClient(this)' data-client='" . $client->getId() . "' style='width:100%'>" . ($client->getName()) . "</a></p>";
                    }
                ?>",
            showConfirmButton: false
        });
        <?php } ?>
    });

    function redirectToRequests(r) {
        if(r == 5) {
        	window.location.href = "./listar_devolucoes.php?f=" + r;
        } else {
        	window.location.href = "./listar_pedidos.php?f=" + r + "&year=<?=$chartYear?>";
        }
    }
    </script>
	<?php include_once ('common_scripts.php'); ?>
</body>
</html>