<!-- ============================================================== -->
<!-- Right sidebar -->
<!-- ============================================================== -->
<!-- .right-sidebar -->
<div class="right-sidebar">
	<div class="slimscrollright">
		<div class="rpanel-title">
			Configurações <span><i class="ti-close right-side-toggle"></i></span>
		</div>
		<div class="r-panel-body">
			<?php
				if($user->isAdmin()) {
					$selectedClient = $user->getClient() > 0 ? $user->getClient() : -1;
					$clients = $cliController->getClients();

					foreach($clients as $client) {
						$class = "";

						if($selectedClient == $client->getId()) {
							$class = "btn-primary";
						} else {
							$class = "btn-secondary";
						}

						echo "<button onClick='changeClient(this)' class='$class btn btn-icon' data-client='" . $client->getId() . "'>" . ($client->getName()) . "</button>";
					}
				}
			?>
			<ul id="themecolors" class="mt-3">
				<li><b>Temas claros</b></li>
				<!--li><a href="javascript:void(0)" data-theme="default" class="default-theme">1</a></li-->
				<li><a href="javascript:void(0)" data-theme="green" class="green-theme">2</a></li>
				<li><a href="javascript:void(0)" data-theme="red" class="red-theme">3</a></li>
				<li><a href="javascript:void(0)" data-theme="blue" class="blue-theme">4</a></li>
				<li><a href="javascript:void(0)" data-theme="purple" class="purple-theme working">5</a></li>
				<li><a href="javascript:void(0)" data-theme="megna" class="megna-theme">6</a></li>
				<li class="d-block mt-4"><b>Temas escuros</b></li>
				<!-- li><a href="javascript:void(0)" data-theme="default-dark"
					class="default-dark-theme">7</a></li-->
				<li><a href="javascript:void(0)" data-theme="green-dark" class="green-dark-theme">8</a></li>
				<li><a href="javascript:void(0)" data-theme="red-dark" class="red-dark-theme">9</a></li>
				<li><a href="javascript:void(0)" data-theme="blue-dark" class="blue-dark-theme">10</a></li>
				<li><a href="javascript:void(0)" data-theme="purple-dark" class="purple-dark-theme">11</a></li>
				<li><a href="javascript:void(0)" data-theme="megna-dark" class="megna-dark-theme ">12</a></li>
				<li class="d-block mt-4"><b>Temas de imagens</b></li>
				<li><a href="javascript:void(0)" data-theme="pixel-tile" class="pixel-tile-theme">13</a></li>
				<li><a href="javascript:void(0)" data-theme="wood-tile" class="wood-tile-theme">14</a></li>
				<li><a href="javascript:void(0)" data-theme="space-tile" class="space-tile-theme">15</a></li>
			</ul>
			<!-- ul class="mt-3 chatonline">
				<li><b>Chat option</b></li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/1.jpg" alt="user-img" class="img-circle">
						<span>Varun Dhavan <small class="text-success">online</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/2.jpg" alt="user-img" class="img-circle">
						<span>Genelia Deshmukh <small class="text-warning">Away</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/3.jpg" alt="user-img" class="img-circle">
						<span>Ritesh Deshmukh <small class="text-danger">Busy</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/4.jpg" alt="user-img" class="img-circle">
						<span>Arijit Sinh <small class="text-muted">Offline</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/5.jpg" alt="user-img" class="img-circle">
						<span>Govinda Star <small class="text-success">online</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/6.jpg" alt="user-img" class="img-circle">
						<span>John Abraham<small class="text-success">online</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/7.jpg" alt="user-img" class="img-circle">
						<span>Hritik Roshan<small class="text-success">online</small></span></a>
				</li>
				<li><a href="javascript:void(0)"><img
						src="assets/images/users/8.jpg" alt="user-img" class="img-circle">
						<span>Pwandeep rajan <small class="text-success">online</small></span></a>
				</li>
			</ul-->
		</div>
	</div>
</div>
<!-- ============================================================== -->
<!-- End Right sidebar -->
<!-- ============================================================== -->