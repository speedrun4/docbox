<div class="modal fade" id="modalListReturns" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog modal-lg" role="document">
		<form id="form-list-returns" method="post" action="#">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Devoluções do pedido</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
					<!-- TABELA DE DEVOLUÇÕES -->
					<div class="col-md-12">
							<table id="data-table-returns" class="table table-bordered table-striped" style="width: 100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Nº</th>
										<th>Usuário</th>
										<th>Data/Hora</th>
										<th>Comprovante</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th>#</th>
										<th>Nº</th>
										<th>Usuário</th>
										<th>Data/Hora</th>
										<th>Comprovante</th>
									</tr>
								</tfoot>
								<tbody>
								</tbody>
							</table>
					</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
<!-- 					<button type="submit" class="btn btn-primary">Cadastrar</button> -->
				</div>
			</div>
		</form>
	</div>
</div>