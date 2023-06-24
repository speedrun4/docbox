<?php
	include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
	include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/control/ClientController.php");
	include_once (dirname(__FILE__) . "/core/control/DocumentTypeController.php");
	include_once (dirname(__FILE__) . "/core/control/DepartmentController.php");

	use Docbox\control\ClientController;
	use Docbox\control\DepartmentController;
	use Docbox\control\DocumentTypeController;
	use function Docbox\control\getUserLogged;
	use Docbox\model\DbConnection;
	use Docbox\model\User;

	$user = getUserLogged();
	if($user == NULL || $user->getClient() <= 0) {
		header("Location: login.php");
	}

	$db = new DbConnection();
	$doctypeController = new DocumentTypeController($db);
	$cliController = new ClientController($db);
	$departController = new DepartmentController($db);
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
                    <div class="col-sm-6 col-8 align-self-center">
                        <h3 class="text-themecolor m-b-0 m-t-0">Retiradas</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="listar_retiradas.php">Retiradas</a></li>
                            <li class="breadcrumb-item active">Cadastrar Retirada</li>
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
                                <h4 class="card-title">Cadastrar Retirada</h4>
                                <h6 class="card-subtitle"> Informe os documentos que devem retirados</h6>
								<form id='form-register' class="mt-4" method='post' action='#' novalidate>
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
                                	<button id="btRegister" type="submit" class="btn btn-primary float-right"><i class='fa fa-paper-plane'></i> Cadastrar</button>
                                </form>
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
    <?php include("modal_register_box.php"); ?>
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
    <script src="js/custom.js"></script>
    <!-- Validation -->
    <script src="js/validation.js"></script>
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!--Custom JavaScript -->
    <script src="js/typeahead.js/typeahead.bundle.js"></script>
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
    !function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
    }(window, document, jQuery);

    function addFormLines() {
		$(".with_year:last").off("focus");
		for (var i = 0; i < 8; i++) {
			$("<div class='row'><div class='col-sm-6'><div class='form-group'><div class='controls'><label for='number'>Número</label><input type='number' class='form-control with_number' placeholder='' min='1' value=''></div></div></div><div class='col-sm-6'><div class='form-group'><div class='controls'><label for='year'>Ano</label><input type='number' class='form-control with_year' placeholder='' min='1900' value=''></div></div></div></div>").insertBefore("#btRegister");
		}
		$(".with_year:last").on("focus", addFormLines);
		blockFormEnter('#form-register');
    }

    $(function() {
    	blockFormEnter('#form-register');

    	$(".with_year:last").on("focus", addFormLines);

		var formRegisterSubmit = function(e) {
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
					swal({
						  title: 'Deseja realmente cadastrar a retirada?',
						  type: 'question',
						  showCancelButton: true,
						  confirmButtonText: 'Sim',
						  cancelButtonText: "Não",
						}).then((result) => {
							if(result.value) {
								// Chama o action
								$.post("./core/actions/registerWithdrawal.php", {numbers: arrNumbers, years: arrYears}, function(res) {
									if(res.ok) {
										swal("Cadastro realizado com sucesso", "", "success").then(() => { window.location.href = "listar_retiradas.php" });
									} else {
										swal("Erro ao realizar cadastro", "", "error");
									}
								}, "json").fail(function() {
									swal("Erro ao realizar pedido", "Por favor verifique sua conexão com o servidor, e tente novamente mais tarde.", "error");
								});
							}
			  			});
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

    	$("#form-register").submit(formRegisterSubmit);
    });
    </script>
    <?php include_once ('common_scripts.php'); ?>
</body>
</html>