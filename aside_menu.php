<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="nav-small-cap">PERSONAL</li>
                <?php if($user->getClient() > 0) { ?>
                <li>
                    <a class="has-arrow" href="#" aria-expanded="false"><i class="mdi mdi-file-document"></i><span class="hide-menu">Documentos</span></a>
                    <ul aria-expanded="false" class="collapse">
                    	<?php if($user->isAdmin()) { ?>
                        <li><a href="cadastro_documento.php">Cadastrar documento</a></li>
                        <?php } ?>
                        <li><a href="pesquisa_documentos.php">Pesquisa geral</a></li>
                    </ul>
                </li>
                    	<?php if($user->isAdmin()) { ?>
                <li>
                    <a class="has-arrow " href="http://google.com" aria-expanded="false"><i class="mdi mdi-book-open-variant"></i><span class="hide-menu">Livros</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="cadastro_livro.php">Cadastrar livro</a></li>
<!--                         <li><a href="listar_livros.php">Listar livros</a></li> -->
                    </ul>
                </li>
                        <?php } ?>
                <li>
                    <a class="has-arrow" href="#" aria-expanded="false"><i class="mdi mdi-package-variant-closed"></i><span class="hide-menu">Caixas</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="listar_caixas.php">Listar caixas</a></li>
                        <?php if($user->isAdmin()) { ?>
						<li><a href="imprimir_etiquetas.php">Imprimir etiquetas</a></li>
						<li><a href="listar_caixas_ano.php">Caixas por ano</a></li>
						<li><a href="auditoria_caixas.php">Auditoria caixas</a></li>
						<?php } ?>
                    </ul>
                </li>

                <li>
                    <a class="has-arrow " href="#" aria-expanded="false"><i class="mdi mdi-file-document-box"></i><span class="hide-menu">Pedidos</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="cadastro_pedido_documentos.php">Pedir documento</a></li>
                        <li><a href="cadastro_pedido.php">Pedir caixas</a></li>
						<li><a href="listar_pedidos.php">Listar pedidos</a></li>
                    </ul>
                </li>
                <li id="menuDevolutions">
                    <a class="has-arrow " href="#" aria-expanded="false"><i class="mdi mdi-swap-horizontal"></i><span class="hide-menu">Devoluções</span></a>
                    <ul aria-expanded="false" class="collapse">
                    	<li><a href="devolver_documentos.php">Devolver documentos</a></li>
                    	<li><a href="devolver_caixas.php">Devolver caixas</a></li>
                        <li><a href="listar_devolucoes.php">Listar devoluções</a></li>
                    </ul>
                </li>
                <li id="menuWithdrawals">
                    <a class="has-arrow " href="#" aria-expanded="false"><i class="mdi mdi-truck-delivery"></i><span class="hide-menu">Retirada</span></a>
                    <ul aria-expanded="false" class="collapse">
                    	<li><a href="cadastro_retirada.php">Pedir retirada de documentos</a></li>
                    	<li><a href="listar_retiradas.php">Listar retiradas</a></li>
                    </ul>
                </li>
                <?php if($user->isAdmin()) { ?>
                <li>
                    <a class="has-arrow " href="#" aria-expanded="false"><i class="mdi mdi-account"></i><span class="hide-menu">Usuários</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="cadastro_usuario.php">Cadastrar usuário</a></li>
						<li><a href="listar_usuarios.php">Listar usuários</a></li>
						<?php if($user->getId() == 1) {?>
						<li><a href="desempenho.php">Desempenho</a></li>
						<?php } ?>
                    </ul>
                </li>
                <li>
                    <a class="has-arrow " href="#" aria-expanded="false"><i class="mdi mdi-settings"></i><span class="hide-menu">Configurações</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="listar_tipos.php">Tipos de documento</a></li>
						<li><a href="listar_departamentos.php">Departamentos</a></li>
                    </ul>
                </li>
                <?php } ?>
                <?php } ?>
                <li class="nav-devider"></li>
                <li class="nav-small-cap">EXTRA COMPONENTS</li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!-- ============================================================== -->
<!-- End Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->