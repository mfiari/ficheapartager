<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<link rel="icon" href="res/img/favicon.ico" />
		<title>Fiche à partager</title>
		
		<script type="text/javascript" src="res/js/jquery-2.1.1.js"></script>
		<script type="text/javascript" src="res/jquery-ui/jquery-ui.js"></script>
		<script type="text/javascript" src="res/js/site.js"></script>
		<script type="text/javascript" src="res/bootstrap/js/bootstrap.js"></script>
		<script type="text/javascript" src="res/sidr/jquery.sidr.min.js"></script>
			
		<link rel="stylesheet" href="res/css/site.css" type="text/css"/>
		<link rel="stylesheet" href="res/jquery-ui/jquery-ui.css" type="text/css"/>
		<link rel="stylesheet" href="res/bootstrap/css/bootstrap.min.css" type="text/css"/>
		<link rel="stylesheet" href="res/sidr/stylesheets/jquery.sidr.dark.min.css" type="text/css"/>
	</head>
	<body>
		<?php include('layouts/login.html'); ?>
		<?php include('layouts/subscribe.php'); ?>
		<div id="page-container">
			<header>
				<div class="container">
					<div>
						<a href="?action=index">
							<img id="logo" src="res/img/logo.png">
						</a>
						<a id="simple-menu" href="#sidr">Toggle menu</a>
					</div>
				</div>
			</header>
			<div id="sidr">
				<!-- Your content -->
				<ul>
					<li class="active"><a href="">Accueil</a></li>
					<li><a data-toggle="modal" data-target="#subscribe-modal">Inscription</a></li>
					<li><a data-toggle="modal" data-target="#login-modal">Connexion</a></li>
					<li><a href="?controler=contact">Contact</a></li>
					<li><a href="?action=faq">FAQ</a></li>
				</ul>
			</div>
			<div id="main-content">
				<div class="container">
					<?php include($request->vue); ?>
				</div>
			</div>
			<footer>
				<div class="container">
					<div class="row">
					</div><br />
					<div class="row">
						<p>Copyright © 2016 - fichepartage.fiarimike.fr ® - Tous droits réservés | <a href="?action=mentions_legales" rel="nofollow">Mentions légales</a> | <a href="?action=cgv" rel="nofollow">CGV</a> | <a href="?action=cgu" rel="nofollow">CGU</a></p>
					</div>
				</div>
			</footer>
		</div>
		<script>
		$(document).ready(function() {
		  $('#simple-menu').sidr();
		});
		</script>
	</body>
</html>
