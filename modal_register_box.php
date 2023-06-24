<div class="modal fade" id="modalRegisterBox" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog modal-lg" role="document">
		<form id="form-register-box" method="post" action="#">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Cadastrar Caixa</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class='col-md-6'>
			            	<div class="form-group">
			                	<div class='controls'>
					                <label for="box_number">N&ordm; da caixa <span class="text-danger">*</span></label>
									<input id='box_number' name='box_number' type="number" class="form-control" placeholder="" required data-validation-required-message="Por favor preencha este campo" min='1'>
								</div>
							</div>
	                    </div>
	                    <div class='col-md-6'>
							<div class="form-group">
								<div class="controls">
									<label class="control-label">Departamento <span class="text-danger">*</span></label>
									<select id='box_department' name='box_department' class="form-control" required	data-validation-required-message="Por favor preencha este campo">
										<option value="" selected>Selecione...</option>
										<?php
                                        	$modalDepartments = $departController->getDepartments($user->getClient());
                                            foreach ($modalDepartments as $iDepartment) {
                                            	echo "<option value='" . $iDepartment->getId() . "'>&nbsp;&nbsp;&nbsp;" . $iDepartment->getName() . "</option>";
                                            }
                                        ?>
									</select>
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