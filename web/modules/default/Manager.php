<?php

include_once WEBSITE_PATH."modules/default/controllers/Template.php";

class Default_Manager {
	
	public function dispatch ($request) {
		if (isset($_GET["controler"])) {
			$controler = $_GET["controler"];
			$controller;
			switch ($controler) {
				case "index" :
					include_once WEBSITE_PATH."modules/default/controllers/Index.php";
					$controller = new Controller_Index();
					$controller->manage($request);
					break;
				case "compte" :
					include_once WEBSITE_PATH."modules/default/controllers/Compte.php";
					$controller = new Controller_Compte();
					$controller->manage($request);
					break;
				case "fiche" :
					include_once WEBSITE_PATH."modules/default/controllers/Fiche.php";
					$controller = new Controller_Fiche();
					$controller->manage($request);
					break;
				case "contact" :
					include_once WEBSITE_PATH."modules/default/controllers/Contact.php";
					$controller = new Controller_Contact();
					$controller->manage($request);
					break;
				default :
					include_once WEBSITE_PATH."modules/default/controllers/Index.php";
					$controller = new Controller_Index();
					$controller->error_404($request);
			}
		} else {
			include_once WEBSITE_PATH."modules/default/controllers/Index.php";
			$controller = new Controller_Index();
			$controller->manage($request);
		}
	}
	
	
}

?>