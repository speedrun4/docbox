<div class="modal fade" id="modal-register-department" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog" role="document">
		<form id="form-register-department" method="post" action="#">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Cadastrar Departamento</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
	                    <div class='col-md-12'>
			            	<div class="form-group">
			                	<div class='controls'>
					                <label for="type_name">Descrição <span class="text-danger">*</span></label>
									<input id='type_name' name='type_name' type="text" class="form-control" placeholder="" required data-validation-required-message="Por favor preencha este campo" maxlength="45" autocomplete="off">
								</div>
							</div>
	                    </div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-primary">Cadastrar</button>
				</div>
			</div>
		</form>
	</div>
</div>