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
				case "restaurant" :
					include_once WEBSITE_PATH."modules/default/controllers/Restaurant.php";
					$controller = new Controller_Restaurant();
					$controller->manage($request);
					break;
				case "panier" :
					include_once WEBSITE_PATH."modules/default/controllers/Panier.php";
					$controller = new Controller_Panier();
					$controller->manage($request);
					break;
				case "commande" :
					if (!$request->_auth) {
						include_once WEBSITE_PATH."modules/default/controllers/Index.php";
						$controller = new Controller_Index();
						$controller->manage($request);
					} else {
						include_once WEBSITE_PATH."modules/default/controllers/Commande.php";
						$controller = new Controller_Commande();
						$controller->manage($request);
					}
					break;
				case "compte" :
					include_once WEBSITE_PATH."modules/default/controllers/Compte.php";
					$controller = new Controller_Compte();
					$controller->manage($request);
					break;
				case "contact" :
					include_once WEBSITE_PATH."modules/default/controllers/Contact.php";
					$controller = new Controller_Contact();
					$controller->manage($request);
					break;
				case "paypal" :
					include_once WEBSITE_PATH."modules/default/controllers/Paypal.php";
					$controller = new Controller_Paypal();
					$controller->manage($request);
					break;
				case "precommande" :
					include_once WEBSITE_PATH."modules/default/controllers/PreCommande.php";
					$controller = new Controller_Pre_Commande();
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