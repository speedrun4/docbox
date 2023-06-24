<aside id="sidebar" class="sidebar c-overflow">
    <div class="s-profile">
        <a href="" data-ma-action="profile-menu-toggle">
            <div class="sp-pic">
                <img src="img/avatar.png" alt="">
            </div>
    
            <div class="sp-info">
                <span style=" font-size: 15px"><?= $user->getName(); ?></span>
<!--                 <i class="zmdi zmdi-caret-down"></i> -->
            </div>
        </a>
    
        <!--ul class="main-menu">
            <li>
                <a href="profile-about.html"><i class="zmdi zmdi-account"></i> View Profile</a>
            </li>
            <li>
                <a href=""><i class="zmdi zmdi-input-antenna"></i> Privacy Settings</a>
            </li>
            <li>
                <a href=""><i class="zmdi zmdi-settings"></i> Settings</a>
            </li>
            <li>
                <a href=""><i class="zmdi zmdi-time-restore"></i> </a>
            </li>
        </ul-->
    </div>

	<ul class="main-menu">
		<li id="liMenuHome"><a href="index.php"><i class="zmdi zmdi-home"></i>
				Início</a></li>
		<?php if($user->getClient() > 0) { ?>
		<li id="liMenuDocuments" class="sub-menu"><a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-collection-text"></i>
				Documentos</a>
			<ul>
				<?php if($user->isAdmin()) { ?>
				<li><a href="cadastro_documento.php">Cadastrar documento</a></li>
				<?php } ?>
				<li><a href="pesquisa_documentos.php">Listar documentos</a></li>
			</ul></li>

		<li id="liMenuBoxes" class="sub-menu">
			<a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-grid"></i>Caixas</a>
			<ul>
				<li><a href="listar_caixas.php">Listar caixas</a></li>
				<li><a href="imprimir_etiquetas.php">Imprimir etiquetas</a></li>
			</ul>
		</li>
		<li id="liMenuOrders" class="sub-menu"><a href=""
			data-ma-action="submenu-toggle"><i class="zmdi zmdi-view-list"></i>
				Pedidos</a>
			<ul>
				<li><a href="cadastro_pedido.php">Fazer pedido</a></li>
				<li><a href="listar_pedidos.php">Listar pedidos</a></li>
			</ul>
		</li>
		<?php if($user->isAdmin()) { ?>
		<li id="liMenuSettings" class="sub-menu">
			<a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-settings"></i> Configurações</a>
			<ul>
				<li><a href="listar_tipos.php">Tipos de documento</a></li>
				<li><a href="listar_usuarios.php">Usuários</a></li>
				<li><a href="listar_departamentos.php">Departamentos</a></li>
			</ul>
		</li>
		<?php } ?>
		<!-- li><a href="buttons.html"><i class="zmdi zmdi-crop-16-9"></i> fre</a></li>
	                <li><a href="icons.html"><i class="zmdi zmdi-airplane"></i>Icons</a></li>

                    <li class="sub-menu">
                        <a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-collection-item"></i> Sample Pages</a>
                        <ul>
                            <li><a href="login.html">Login and Sign Up</a></li>
                            <li><a href="lockscreen.html">Lockscreen</a></li>
                            <li><a href="404.html">Error 404</a></li>
                        </ul>
                    </li-->
		<?php } ?>
	</ul>
</aside>