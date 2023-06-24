<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>assets/images/favicon.png">
    <title>Docbox</title>
    <link href="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>css/style.min.css" rel="stylesheet">
    <link href="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>css/custom.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
	<style type="text/css">
		/*******************
		Error Page
		******************/
		.error-box {
		  height: 100%;
		  position: fixed;
		  background: url(../assets/images/background/error-bg.jpg) no-repeat center bottom #fff;
		  width: 100%; }
		  .error-box .footer {
		    width: 100%;
		    left: 0px;
		    right: 0px;
		}
		.error-body {
		  padding-top: 5%; }
		  .error-body h1 {
		    font-size: 210px;
		    font-weight: 900;
		    line-height: 210px; }
	</style>
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="error-box">
            <div class="error-body text-center">
                <h1 class="error-title">404</h1>
                <h3 class="text-uppercase error-subtitle">PÁGINA NÃO ENCONTRADA</h3>
                <p class="text-muted mt-4 mb-4">Fizemos um direcionamento incorreto --ou-- você requisitou uma página que não existe.</p>
                <p class="text-muted mt-4 mb-4">Recomendamos que clique no botão abaixo para voltar à página inicial.</p>
                <a href="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>" class="btn btn-danger btn-rounded waves-effect waves-light mb-5">Voltar ao início</a> </div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="<?= 'https://' . $_SERVER['SERVER_NAME'] . '/'; ?>assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="<?= "https://" . $_SERVER['SERVER_NAME'] . "/"; ?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>
    $('[data-toggle="tooltip"]').tooltip();
    $(".preloader").fadeOut();
    </script>
</body>

</html>