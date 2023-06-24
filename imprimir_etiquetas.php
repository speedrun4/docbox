<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");
include_once (dirname(__FILE__) . "/core/model/User.php");

use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;
use Docbox\model\User;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0 || $user->getProfile() != User::USER_ADMIN) {
	header("Location: login.php");
}

$db = new DbConnection();
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <?php include('head.php'); ?>
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
                        <h3 class="text-themecolor m-b-0 m-t-0">Etiquetas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Caixas</a></li>
                            <li class="breadcrumb-item active">Impressão de etiquetas</li>
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
                                <h4 class="card-title">Etiquetas com números</h4>
                                <h6 class="card-subtitle">Informe os dados das etiquetas.</h6>
                                <form id='form-create-labels' class="mt-4" method='post' action='impressao_etiquetas.php' target='_blank' autocomplete="off" novalidate>
                                	<div class='row'>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="lab_number">N&ordm; inicial da caixa <span class="text-danger">*</span></label>
			                                        <input id='lab_number' name='lab_number' type="number" class="form-control" placeholder="0" min='1' value='' required data-validation-required-message="Por favor preencha este campo">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="lab_num_pages">Qtd. de páginas <span class="text-danger">*</span></label>
			                                        <input id='lab_num_pages' name='lab_num_pages' type="number" class="form-control" placeholder="0" min='1' value='' required data-validation-required-message="Por favor preencha este campo">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-2'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="lab_number_final">N&ordm; final da caixa <span class="text-danger">*</span></label>
			                                        <input id='lab_number_final' name='lab_number_final' type="number" class="form-control" placeholder="0" min='1' value='' required data-validation-required-message="Por favor preencha este campo">
		                                    	</div>
		                                    </div>
                                		</div>
                                	</div>
                                    <button type="submit" class="btn btn-primary">Gerar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Etiquetas com texto</h4>
                                <h6 class="card-subtitle">Informe o texto da etiqueta.</h6>
                                <form id='form-create-labels-text' class="mt-4" method='post' action='impressao_etiquetas.php' target='_blank' novalidate>
                                	<input type="hidden" id="lab_position" name="lab_position">
                                	<div class='row'>
                                		<div class='col-md-12'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="lab_text">Texto <span class="text-danger">*</span></label>
			                                        <input id='lab_text' name='lab_text' type="text" class="form-control" placeholder="Texto da etiqueta" value='' required data-validation-required-message="Por favor preencha este campo" autocomplete="off">
		                                    	</div>
		                                    </div>
                                		</div>
                                	</div>
                                    <a href='#modalChooseLocation' data-toggle='modal' class="btn btn-primary">Gerar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
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
    <?php include("modal_label_location.php"); ?>
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
    <script type="text/javascript">
    ! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()
    }(window, document, jQuery);
    $(function() {
    	blockFormEnter('#form-create-labels, #form-create-labels-text');

    	$("#lab_num_pages").change(function() {
			$("#lab_number_final").val(parseInt($("#lab_number").val()) + $(this).val() * 4 - 1);
		});

		$("#lab_number").change(function() {
			$("#lab_number_final").val(parseInt($(this).val()) + $("#lab_num_pages").val() * 4 - 1);
		});

    	$(".pageLabel").click(function() {
            $("#lab_position").val($(this).data().value);
            $("#form-create-labels-text").submit();
            $("#modalChooseLocation").modal('hide');
        });
    });
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>