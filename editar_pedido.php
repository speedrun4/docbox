<?php
	include_once (dirname(__FILE__) . "/core/model/DbConnection.php");
	include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/control/RequestController.php");

	$user = getUserLogged();
	if($user == NULL || $user->getClient() <= 0) {
		header("Location: login.php");
	}

	$db = new DbConnection();
	$reqController = new RequestController($db);
	$request = $reqController->getRequest(intval($_GET['r']));

	if($request == NULL) {
		header("Location: login.php");
	}
?>
<!DOCTYPE html>
<html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
    	<?php include("head.php"); ?>
        <link href="vendors/bower_components/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">
    </head>
    <body>
        <?php include("header.php"); ?>

        <section id="main">
            <?php include("aside_profile.php"); ?>

            <section id="content">
                <div class="container">
                    <div class="block-header">
                        <h2>Visualizar Requisição</h2>
                    </div>

					<div class="card">
                        <div class="card-header">
                            <h2>Requisição
                            <small>Utilize os filtros abaixo para pesquisar nos documentos.</small>
                            </h2>
                        </div>
                        <div class="card-body card-padding">
                        	<form id="form-register-document" method="post" action="#" class="row" role="form">
                            	<div class="col-sm-1">
                                    <div class="form-group fg-float">
                                        <div class="fg-line">
                                            <input id="doc_box" type="number" class="form-control form-control--highlighted" value="<?php echo $request->getId(); ?>" disabled>
                                            <label class="fg-label">N&ordm; Requisição</label>
                                        </div>
                                        <small class="help-block">Informe n&ordm; caixa</small>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group fg-float">
                                        <div class="fg-line">
                                            <input type="text" class="form-control form-control--highlighted" value="<?php echo $request->getDatetime()->format("d/m/Y"); ?>" disabled>
                                            <label class="fg-label">Data</label>
                                        </div>
                                        <small class="help-block">Informe o número</small>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="form-group fg-float">
                                        <div class="fg-line">
                                            <input type="text" class="form-control form-control--highlighted" value="<?php echo $request->getDatetime()->format("H:i"); ?>" disabled>
                                            <label class="fg-label">Hora</label>
                                        </div>
                                        <small class="help-block">Informe o número</small>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group fg-float">
                                        <div class="fg-line">
                                            <input type="text" class="form-control form-control--highlighted" value="<?php echo $request->getUser()->getName(); ?>" disabled>
                                            <label class="fg-label">Usuário</label>
                                        </div>
                                        <small class="help-block">Informe o número</small>
                                    </div>
                                </div>
                                <?php if($user->usr_profile == 1) { ?>
                                <div class="col-sm-3">
                                    <div class="form-group fg-float">
                                        <div class="fg-line fg-toggled">
                                            <div class="select">
                                                <select id="req_status" name="req_status" class="form-control">
                                                    <?php 
                                                    $status = $reqController->getRequestStatus();
                                                    foreach($status as $s) {
                                                    	$selected = "";
                                                    	if($s->getId() == $request->getStatus()) {
                                                    		$selected = "selected";
                                                    	}
                                                    	echo "<option value='" . $s->getId() . "' $selected>" . $s->getName() . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <label class="fg-label">Tipo de documento (*)</label>
                                            </div>
                                        </div>
                                        <small class="help-block">Informe tipo de documento</small>
                                    </div>
                                </div>
                                <?php } else if($user->usr_profile == 2) { ?>
                            	<div class="col-sm-3">
                                    <div class="form-group fg-float">
                                        <div class="fg-line">
                                            <input type="text" class="form-control form-control--highlighted" value="<?php
                                            $status = $reqController->getRequestStatus();
                                            foreach($status as $s) {
                                            	if($s->getId() == $request->getStatus()) {
                                            		echo $s->getName(); break;
                                            	}
                                            }
                                            ?>" disabled>
                                            <label class="fg-label">Status</label>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
								<?php
								if (($request->getStatus() == 1 && $request->getUser()->getId() == $user->getId()) || $user->getProfile() == User::USER_ADMIN) {
								?>
	                            	<button id="btCancelRequest" type="button" class="btn btn-danger btn-sm m-t-10 pull-right">CANCELAR</button>
	                            <?php
								}
								?>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2>Documentos
                                <small>Lista dos documentos solicitados pelo usuário</small>
                            </h2>
                            <ul class="actions">
								<li class="dropdown"><a href="" data-toggle="dropdown"> <i
										class="zmdi zmdi-more-vert"></i>
								</a>
	
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="alterar_requisicao.php?r=<?php echo $request->getId(); ?>">Alterar requisição</a></li>
								</ul></li>
							</ul>
                        </div>

                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ações</th>
                                    <th>Caixa</th>
                                    <th>Tipo</th>
                                    <th>Número</th>
                                    <th>Ano</th>
                                    <th>Letra</th>
                                    <th>Local</th>
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
                                    <th>Local</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
            </section>
        </section>

        <?php include("footer.php"); ?>

        <!-- Javascript Libraries -->
        <script src="vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

        <script src="vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
        <script src="vendors/bower_components/sweetalert2/dist/sweetalert2.min.js"></script>
        <script src="vendors/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>

        <!-- Placeholder for IE9 -->
        <!--[if IE 9 ]>
            <script src="vendors/bower_components/jquery-placeholder/jquery.placeholder.min.js"></script>
        <![endif]-->

        <script src="js/app.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var tbDocuments = $('#data-table-basic').DataTable({
                	"serverSide": true,
                	"processing": true,
                	"aaSorting": [2, "desc"],
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
                	    },
                	    "oAria": {
                	        "sSortAscending": ": Ordenar colunas de forma ascendente",
                	        "sSortDescending": ": Ordenar colunas de forma descendente"
                	    }
                	},
                	"sAjaxSource": "./core/data-tables/TableRequestBoxes.php",
                	"sServerMethod": "POST",
                	"fnServerParams": function(aoData) {
        				aoData.push(
        					{"name": "r", "value" : <?php echo $request->getId(); ?>});
                	}, 
                	"aoColumnDefs" : [{
	                    	"aTargets": [0],
	                    	"visible":false
                    	}, {
                    		"aTargets": [1],
							"visible" :false
                        }, {
	                        "aTargets": [2],
	                        width: 72,
	                        "mRender": function(d,t,f) {
	                            return "<center>" + d + "<center>";
	                        }
                        }
                    ]
                });
                $("#form-register-document").submit(function(e) {
                	tbDocuments.ajax.reload();
                    e.preventDefault();
                });
                $("#form-register-document").on("reset", function() {
        			$("#doc_type").parents(".fg-line").addClass("fg-toggled");
        			$("#doc_location").parents(".fg-line").addClass("fg-toggled");
        		});
        		$("#btCancelRequest").click(function() {
        			swal({
	        			title: 'Deseja realmente cancelar o pedido?',
	        			text: "",
	        			type: 'question',
	        			showCancelButton: true,
	        			confirmButtonColor: '#3085d6',
	        			cancelButtonColor: '#aaa',
	        			confirmButtonText: 'Sim',
	        			cancelButtonText: "Não",
	        			showLoaderOnConfirm: true
        			}).then((result) => {
        				$.post("./core/actions/cancelRequest.php?r=<?php echo intval($_GET['r']);  ?>", {}, function(data) {
      			        	if(data.ok) {
          			        	swal("Pedido cancelado com sucesso!", "", "success");
          			        	$("#req_status").val("CANCELADO");
      			        	} else {
								swal(data.error, "", data.type);
      			        	}
      			        }, 'json').fail(function(xhr, status, error) {
      			        	swal("Erro ao realizar pedido",
      	      			        	"Por favor tente novamente mais tarde." +
      	      			        	" Se o problema persistir entre em contato com o suporte do programa.",
      	      			        	"error");
	      			    });
        			});
            	});
            });
        </script>
    </body>
  </html>