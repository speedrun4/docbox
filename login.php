<?php
if (empty ( $_SERVER ['HTTPS'] ) || $_SERVER ['HTTPS'] == 'off') {
	if (! file_exists ( dirname ( __FILE__ ) . "/DEVMACHINE.inc" )) {
		header ( "Location: https://" . $_SERVER ['SERVER_NAME'] . $_SERVER ['PHP_SELF'] );
	}
}

include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");
use function Docbox\control\getUserLogged;

$user = getUserLogged ();
if ($user != NULL) {
	header ( "Location: index.php" );
}
?><!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title>DocBox</title>
    <!-- Bootstrap Core CSS -->
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.min.css" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="css/colors/green.css" id="theme" rel="stylesheet">
    <link href="assets/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        #wrapper {
            background-image: url(../assets/images/background/login-register.png);
            background-size: contain;
            justify-content: center;
            height: 100%;
            position: absolute;
            background-color: cadetblue;
        }

        .card-body {
            background-color: white;
            border-radius: 10px;
        }

        .login-register {
            background-color: steelblue;
        }
    </style>
</head>

<body>
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
    <section id="wrapper">
        <div class="login-register">        
            <div class="login-box">
            <div class="card-body">
            	<div class="text-center col-6 offset-3 col-md-6 offset-md-3">
            		<img class="img-fluid" alt="" src="assets/images/docbox_logo.png" height="256" style="margin: 10px">
            	</div>
                <form id="loginform" action="#" class="form-horizontal form-material" novalidate>
                    <h3 class="box-title mb-3">Acesso</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input id="user" class="form-control" type="text" placeholder="Usuário" autocomplete="off" data-validation-required-message="Campo obrigatório" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input id="pass" class="form-control" type="password" required="" placeholder="Senha" autocomplete="current-password"> </div>
                    </div>
                    <div class="form-group text-center mt-3">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Entrar</button>
                        </div>
                    </div>
                </form>
            </div>
          </div>
        </div>
        
    </section>
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
    <script src="js/validation.js"></script>
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <script type="text/javascript">
	    !function(window, document, $) {
	        "use strict";
	        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
	        $("#loginform").submit(function(e) {
	        	$.post("./core/actions/doLogin.php", {user: $("#user").val(), pass: $("#pass").val()}, function(data) {
                	if(data.ok) {
                    	window.location.href = "index.php";
                    } else {
                    	swal("", "Usuário ou senha incorretos!", "error");
                    }
                }, 'json').fail(function() {
                	swal("", "Erro de conexão", "error");
                });
                e.preventDefault();
		    });
		    $("#user").focus();
	    } (window, document, jQuery);
    </script>
</body>
</html>