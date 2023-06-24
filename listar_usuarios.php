<?php
include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
include_once (dirname(__FILE__) . "/core/control/UserController.php");
include_once (dirname(__FILE__) . "/core/control/ClientController.php");
include_once (dirname(__FILE__) . "/core/control/UserSession.php");

use Docbox\control\ClientController;
use Docbox\control\UserController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged();
if($user == NULL || $user->getClient() <= 0) {
	header("Location: login.php");
}

$db = new DbConnection();
$usrController = new UserController($db);
$cliController = new ClientController($db);
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
                        <h3 class="text-themecolor mb-0 mt-0">Usuários</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Configurações</a></li>
                            <li class="breadcrumb-item active">Listar Usuários</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                        <a href='cadastro_usuario.php' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Novo</a>
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
                                <h6 class="card-subtitle">Utilize os filtros abaixo para pesquisar os usuários.</h6>
                                <form id='form-filter-document' method='post' action='#'>
                                	<div class='row'>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Cliente</label>
		                                            <select id='userclient' name='userclient' class="form-control">
		                                                <option value="" selected>Selecione o cliente</option>
                                                        <?php
	                                            			$clients = $cliController->getClients();
	                                            			foreach($clients as $client) {
	                                            				echo "<option value='" . $client->getId() . "'>" . $client->getName() . "</option>";
	                                            			}
			                                            ?>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
	                                		<div class="form-group">
		                                        <div class="controls">
		                                            <label class="control-label">Perfil</label>
		                                            <select id='userprofile' name='userprofile' class="form-control">
		                                                <option value="" selected>Selecione o perfil</option>
                                                        <option value="1">Usuário Administrador</option>
    		                                            <option value="2">Usuário Comum</option>
		                                            </select>
		                                        </div>
	                                   		</div>
                                    	</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                    	<div class='controls'>
			                                        <label for="username">Nome do usuário</label>
			                                        <input id='username' name='username' type="text" class="form-control" placeholder="">
		                                    	</div>
		                                    </div>
                                		</div>
                                		<div class='col-md-3'>
		                                    <div class="form-group">
		                                        <label for="userlogin">Login do usuário</label>
		                                        <input id='userlogin' name='userlogin' type="text" class="form-control">
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
		                                    <th>Cliente</th>
		                                    <th>Nome</th>
		                                    <th>Login</th>
		                                    <th>Perfil</th>
		                                    <th>Departamentos</th>
		                                    <th>Últ. login</th>
		                                </tr>
		                                </thead>
		                                <tfoot>
		                                <tr>
		                                    <th>#</th>
		                                    <th>Ações</th>
		                                    <th>Cliente</th>
		                                    <th>Nome</th>
		                                    <th>Login</th>
		                                    <th>Perfil</th>
		                                    <th>Departamentos</th>
		                                    <th>Últ. login</th>
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
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(document).ready(function() {
        	var tbDocuments = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [3, "asc"],
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
            	    }, "oAria": {
            	        "sSortAscending": ": Ordenar colunas de forma ascendente",
            	        "sSortDescending": ": Ordenar colunas de forma descendente"
            	    }
            	},
            	"sAjaxSource": "./core/data-tables/TableUsers.php",
            	"sServerMethod" : "POST",
            	"fnServerParams" : function(aoData) {
    				aoData.push(
    					{"name": "username", "value" : $("#username").val()},
    					{"name": "userlogin", "value" : $("#userlogin").val()},
    					{"name": "userprofile", "value" : $("#userprofile").val()});
            	},
            	"aoColumnDefs" : [{
                    	"aTargets": [0],
                    	"visible":false
                	}, {
                		"aTargets": [1],
                		orderable: false,
                        width: 72,
                        "mRender": function(d,t,f) {
                            return "<a href='alterar_usuario.php?user=" + d + "' class='btn btn-circle btn-warning'><i class='fa fa-edit'></i></a>";
                        }
                    }, {
                        "aTargets":[5],
                        "mRender":function(d,t,f) {
                            if(d == 1) {
                                return "Administrador";
                            } else {
                                return "Usuário comum";
                            }
                         }
                    }]
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
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>
