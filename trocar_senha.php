<?php
	include_once (dirname(__FILE__) . "/core/control/UserSession.php");
	include_once (dirname(__FILE__) . "/core/utils/Utils.php");

	$user = getUserLogged();
	if($user != NULL) {
		header("Location: index.php");
	}

	$token = getReqParam("token", "str", "get");
	if(empty($token)) {
	    header("Location: index.php");
	    exit;
	}
?>
<!DOCTYPE html>
<html>
    <!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <?php include("head.php"); ?>
        <style type="text/css">
            .inform {
                font-weight: bold;
                font-size: 2rem;
            }
        </style>
    </head>

    <body>
        <div class="login-content">
            <!-- Login -->
            <div class="lc-block toggled" id="l-login">
                <div class="lcb-form">
                	<form id="form-change-pass" action="#" method="post" onsubmit="return changePassSubmit();">
                		<input name='token' type="hidden" value='<?= $token ?>'>
		            	<div>
		            		<img alt="" src="img/scanfile-logo.png" height="92" style="margin: 10px">
		            	</div>
		            	<p class='inform'>Por favor informe a nova senha</p>

	                    <div class="input-group m-b-20">
	                        <span class="input-group-addon"><i class="zmdi zmdi-male"></i></span>
	                        <div class="fg-line">
	                            <input id="password" name='password' type="password" class="form-control" placeholder="Senha" value="">
	                        </div>
	                    </div>

	                    <div class="input-group m-b-20">
	                        <span class="input-group-addon"><i class="zmdi zmdi-male"></i></span>
	                        <div class="fg-line">
	                            <input id="conf-password" name='conf-password' type="password" class="form-control" placeholder="Repita Senha" value="">
	                        </div>
	                    </div>

	                    <div id="errorDiv" style="margin-top: 8px; color: red; display: none">
	                        <label>
	                            <i class="input-helper"></i>
	                            <span id="errorText">As senhas não conferem</span>
	                        </label>
	                    </div>

	                    <button id="btDoLogin" type="submit" class="btn btn-login btn-success btn-float"><i class="zmdi zmdi-arrow-forward"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Older IE warning message -->
        <!--[if lt IE 9]>
            <div class="ie-warning">
                <h1 class="c-white">Warning!!</h1>
                <p>You are using an outdated version of Internet Explorer, please upgrade <br/>to any of the following web browsers to access this website.</p>
                <div class="iew-container">
                    <ul class="iew-download">
                        <li>
                            <a href="http://www.google.com/chrome/">
                                <img src="img/browsers/chrome.png" alt="">
                                <div>Chrome</div>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.mozilla.org/en-US/firefox/new/">
                                <img src="img/browsers/firefox.png" alt="">
                                <div>Firefox</div>
                            </a>
                        </li>
                        <li>
                            <a href="http://www.opera.com">
                                <img src="img/browsers/opera.png" alt="">
                                <div>Opera</div>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.apple.com/safari/">
                                <img src="img/browsers/safari.png" alt="">
                                <div>Safari</div>
                            </a>
                        </li>
                        <li>
                            <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                                <img src="img/browsers/ie.png" alt="">
                                <div>IE (New)</div>
                            </a>
                        </li>
                    </ul>
                </div>
                <p>Sorry for the inconvenience!</p>
            </div>
        <![endif]-->

        <!-- Javascript Libraries -->
        <script src="vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="vendors/bower_components/Waves/dist/waves.min.js"></script>

        <!-- Placeholder for IE9 -->
        <!--[if IE 9 ]>
            <script src="vendors/bower_components/jquery-placeholder/jquery.placeholder.min.js"></script>
        <![endif]-->

        <script src="js/app.js"></script>

		<!--sweetalert -->
		<script type="text/javascript" src="vendors/bower_components/sweetalert2/dist/sweetalert2.min.js"></script>

        <script type="text/javascript">
            function changePassSubmit() {
            	$("#errorDiv").hide();
            	if($("#password").val() == "") {
                	$("#password").focus();
                	$("#errorText").html("Informe a nova senha");
                	$("#errorDiv").show();
                } else if($("#conf-password").val() == "") {
                	$("#conf-password").focus();
                	$("#errorText").html("Repita a senha");
                	$("#errorDiv").show();
                } else {
                    if($("#conf-password").val() == $("#password").val()) {
						var value = $("#password").val();
                    	if(value.indexOf(' ') > -1) return false;
        				var expReg = /([^a-zA-z0-9])/;
        				if(value.match(expReg)) {
            				$("#errorText").html("Por favor utilize somente letras e números!");
                        	$("#errorDiv").show();
            			} else {
                			if($("#password").val().length >= 6) {
                				$.post("./core/actions/changePassword.php", $("#form-change-pass").serialize(), function(data) {
                                	if(data.ok) {
                                		swal({
            								title : data.msg,
            								type : "success",
            							}).then(function() {
            	  			        		window.location.href = "login.php";
            							});
                                    } else {
                                    	$("#password").focus();
                                    	$("#password").val("");
                                    	$("#errorText").html(data.msg);
                                    	$("#errorDiv").show();
                                    }
                                }, 'json').fail(function() {
                                	$("#errorText").html("Falha de comunicação com o servidor");
                                	$("#errorDiv").show();
                                });
                    		} else {
                    			$("#errorText").html("A senha deve conter no mínimo 6 caracteres");
                            	$("#errorDiv").show();
                    		}
            			}
                    } else {
                    	$("#errorText").html("As senhas não conferem!");
                    	$("#errorDiv").show();
                    }
                }
                return false;
            }
        </script>
    </body>
</html>
