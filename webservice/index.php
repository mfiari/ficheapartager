<?php

include_once "../config.php";
include_once ROOT_PATH."function.php";
include_once 'controllers/Template.php';
include_once 'controllers/PublicCategorie.php';
include_once 'controllers/User.php';

session_start();

if (isset($_GET["module"])) {
	$module = $_GET["module"];
	$controller;
	switch ($module) {
		case "user" :
			$controller = new Controller_User();
			break;
		case "public_categorie" :
			$controller = new Controller_Public_Categorie();
			break;
		case "public_fiche" :
			$controller = new Controller_Public_Fiche();
			break;
	}
	$controller->handle();
} else {
	
	
}


?>