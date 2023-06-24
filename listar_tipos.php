<?php
include_once (dirname ( __FILE__ ) . "/core/model/DbConnection.php");
include_once (dirname ( __FILE__ ) . "/core/control/DocumentTypeController.php");
include_once (dirname ( __FILE__ ) . "/core/control/UserSession.php");

use Docbox\control\DocumentTypeController;
use function Docbox\control\getUserLogged;
use Docbox\model\DbConnection;

$user = getUserLogged ();
if ($user == NULL || $user->getClient () <= 0) {
	header ( "Location: login.php" );
}

$db = new DbConnection ();
$doctypeController = new DocumentTypeController ( $db );
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
                        <h3 class="text-themecolor mb-0 mt-0">Tipos de Documentos</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Configurações</a></li>
                            <li class="breadcrumb-item active">Listar tipos de documentos</li>
                        </ol>
                    </div>
                    <div class="col-md-6 col-4 align-self-center">
                        <button type='button' data-toggle='modal' data-target='#modal-register-type' class="btn float-right hidden-sm-down btn-success"><i class="mdi mdi-plus-circle"></i> Novo</button>
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
                                <h4 class="card-title">Resultados</h4>
                                <h6 class="card-subtitle">Esta tabela exibe todos os tipos de documentos cadastrados</h6>
                                <div class="table-responsive m-t-40">
                                    <table id="data-table-basic" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Descrição</th>
			                                    <th>Prefixo</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
			                                <tr>
			                                    <th>#</th>
			                                    <th>Ações</th>
			                                    <th>Descrição</th>
			                                    <th>Prefixo</th>
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
    <?php
	    include("modal_register_type.php");
	    include("modal_update_type.php");
    ?>
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
    <!-- Sweet-Alert  -->
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="assets/plugins/sweetalert/jquery.sweet-alert.custom.js"></script>
    <script src="js/session.min.js"></script>
    <script src="js/app.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
    	var tbTypes = null;
        $(document).ready(function() {
        	tbTypes = $('#data-table-basic').DataTable({
            	"serverSide": true,
            	"processing": true,
            	"aaSorting": [2, "asc"],
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
            	"sAjaxSource": "./core/data-tables/TableDocumentTypes.php",
            	"sServerMethod": "POST",
            	"aoColumnDefs" : [{
                    	"aTargets": [0],
                    	"visible":false,
                    	orderable: false
                	}, {
                		"aTargets": [1],
                        width: 140,
                		orderable: false,
                        "mRender": function(d,t,f) {
                            return "<a onclick='showUpdateTypeModal(this)' class='btn btn-primary btn-circle btn-small' href='#' data-document='" +f[0]+"' data-name='" + f[2] + "' data-preffix='" + f[3] + "'><i class='fa fa-edit'></i></a>" +
	                            "<a onclick='deleteDocumentType(this)' class='btn btn-circle btn-small btn-danger waves-effect' href='#' data-type='" +f[0]+"' data-name='" + f[2] + "'><i class='fa fa-trash'></i></a>";
                        }
                    }, {
                    	"aTargets": [2],
                    	orderable: true
                    }
                ]
            });
            $("#form-register-document").submit(function(e) {
            	tbTypes.ajax.reload();
                e.preventDefault();
            });
            $("#form-register-document").on("reset", function() {
    			$("#doc_type").parents(".fg-line").addClass("fg-toggled");
    			$("#doc_location").parents(".fg-line").addClass("fg-toggled");
    		});
    		$("#form-register-type").submit(function(e) {
    		    $("#modal-register-type").modal('hide');
    			swal({
    				title: "Aguarde...",
    			   	showConfirmButton: false,
    			   	allowOutsideClick: false,
    			   	onOpen: function() {
    			   		swal.showLoading();
    			   		// Registra a caixa
    			   		$.post('./core/actions/registerType.php', $("#form-register-type").serialize(), function(res) {
    			      		if(res.ok) {
    			        		swal("Registro realizado com sucesso!", "", "success");
    			        		tbTypes.ajax.reload();
    			        	} else {
    			        		swal("Erro ao realizar a operação", res.message, res.type);
    				        }
    			       	}, 'json').fail(function(){
    			       		swal("Erro ao realizar a operação", "Por favor tente novamente mais tarde. Se o problema persistir entre em contato com o suporte do programa.", "error");
    			       	});
    			    }
    			});
        		e.preventDefault();
        	});
    		$("#btCreateType").click(() => {
    			$("#modal-register-type").modal();
        	});
            $("#liMenuSettings").addClass("active toggled").find("li:nth-child(1)").addClass("active");
        });
        function showUpdateTypeModal(elem) {
        	$("#up_type_id").val($(elem).data().document);
        	$("#up_type_name").val($(elem).data().name);
            $("#up_type_preffix").val($(elem).data().preffix);
            $("#modal-update-type").modal('show');
        }
        function deleteDocumentType(elem) {
        	swal({
    			title: 'Deseja realmente excluir o documento?',
    			text: "",
    			type: 'question',
    			showCancelButton: true,
    			confirmButtonColor: '#3085d6',
    			cancelButtonColor: '#aaa',
    			confirmButtonText: 'Sim',
    			cancelButtonText: "Não",
    			showLoaderOnConfirm: true
			}).then((result) => {
				if(result.value) {
					$.post("./core/actions/deleteType.php", {t:$(elem).data().type}, 
		    			function(data) {
							swal(data.message, "", data.type);
							tbTypes.ajax.reload();
			        }, 'json').fail(function(xhr, status, error) {
				        	swal("Erro ao realizar solicitação", "Por favor tente novamente mais tarde." +
		      			        	" Verifique sua conexão com a internet.", "error");
	  			    });
				}
			});
        }
    </script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <?php include_once ('common_scripts.php'); ?>
</body>

</html>
