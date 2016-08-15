<?php

include_once ROOT_PATH."models/Template.php";
include_once ROOT_PATH."models/Panier.php";
include_once ROOT_PATH."models/Restaurant.php";
include_once ROOT_PATH."models/Horaire.php";
include_once ROOT_PATH."models/Carte.php";
include_once ROOT_PATH."models/Supplement.php";
include_once ROOT_PATH."models/Menu.php";
include_once ROOT_PATH."models/Commande.php";
include_once ROOT_PATH."models/GCMPushMessage.php";
include_once ROOT_PATH."models/Format.php";
include_once ROOT_PATH."models/Option.php";
include_once ROOT_PATH."models/OptionValue.php";
include_once ROOT_PATH."models/Accompagnement.php";


class Controller_Panier extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "view" :
					$this->view($request);
					break;
				case "addCarte" :
					$this->addCarte($request);
					break;
				case "showCarteDetail" :
					$this->showCarteDetail($request);
					break;
				case "removeCarte" :
					$this->removeCarte($request);
					break;
				case "addMenu" :
					$this->addMenu($request);
					break;
				case "removeMenu" :
					$this->removeMenu($request);
					break;
				case "commande" :
					$this->commande($request);
					break;
			}
		}
	}
	
	public function view ($request) {
		$request->disableLayout = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$request->panier = $panier->load();
		if (isset($_SESSION['search_adresse'])) {
			$request->adresse = $_SESSION['search_adresse'];
		}
		if (isset($_SESSION['search_ville'])) {
			$request->ville = $_SESSION['search_ville'];
		}
		if (isset($_SESSION['search_cp'])) {
			$request->codePostal = $_SESSION['search_cp'];
		}
		if (isset($_SESSION['search_rue'])) {
			$request->rue = $_SESSION['search_rue'];
		}
		$request->vue = $this->render("panier.php");
	}
	
	private function initPanier ($request, $id_restaurant) {
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->init();
		if ($panier->id_restaurant == -1) {
			$restaurant = new Model_Restaurant();
			$restaurant->id = $id_restaurant;
			$fields = array ("latitude", "longitude");
			$restaurant->get($fields);
			$adresseResto = $restaurant->latitude.','.$restaurant->longitude;
			
			$user_latitude = $_SESSION['search_latitude'];
			$user_longitude = $_SESSION['search_longitude'];
			$adresseUser = $user_latitude.','.$user_longitude;
			
			$result = getDistance($adresseUser, $adresseResto);
			$distance = 0;
			if ($result['status'] == "OK") {
				$distance = $result['distance'] / 1000;
			}
			$panier->id_restaurant = $id_restaurant;
			$panier->rue = $_SESSION['search_rue'];
			$panier->ville = $_SESSION['search_ville'];
			$panier->code_postal = $_SESSION['search_cp'];
			$panier->latitude = $user_latitude;
			$panier->longitude = $user_longitude;
			$panier->distance = $distance;
			$panier->update();
		} else if ($panier->id_restaurant != $id_restaurant) {
			$this->error(400, "Bad request");
		}
		return $panier;
	}
	
	public function addCarte ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (!isset($_POST['id_carte'])) {
			$this->error(409, "Conflict");
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$id_restaurant = $_POST['id_restaurant'];
		$panier = $this->initPanier ($request, $id_restaurant);
		$quantite = $_POST['quantite'];
		$id_carte = $_POST['id_carte'];
		$format = $_POST['format'];
		$modelCarte = new Model_Carte();
		$modelCarte->id = $id_carte;
		$modelCarte->load();
		$id_panier_carte = $panier->addCarte($id_carte, $format, $quantite);
		foreach ($modelCarte->options as $option) {
			foreach ($option->values as $value) {
				if (isset($_POST['check_option_'.$option->id.'_'.$value->id])) {
					$panier->addCarteOption($id_panier_carte, $option->id, $value->id);
				}
			}
		}
		foreach ($modelCarte->accompagnements as $accompagnement) {
			foreach ($accompagnement->cartes as $carte) {
				if (isset($_POST['check_accompagnement_'.$carte->id])) {
					$panier->addCarteAccompagnement($id_panier_carte, $carte->id);
				}
			}
		}
		$carte = $modelCarte->getSupplements();
		foreach ($carte->supplements as $supplement) {
			var_dump($supplement);
			if (isset($_POST['check_supplement_'.$supplement->id])) {
				$panier->addCarteSupplement($id_panier_carte, $supplement->id);
			}
		}
	}
	
	public function showCarteDetail ($request) {
		
	}
	
	public function removeCarte ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (!isset($_POST['id_panier'])) {
			$this->error(400, "Bad request");
		}
		if (!isset($_POST['id_panier_carte'])) {
			$this->error(400, "Bad request");
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->id = $_POST['id_panier'];
		$id_panier_carte = $_POST['id_panier_carte'];
		if (!$panier->removePanierCarte($id_panier_carte)) {
			$this->error(500, "Bad request");
		}
		$totalArticle = $panier->getNbArticle();
		if ($totalArticle == 0) {
			$panier->remove();
		}
	}
	
	public function addMenu ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (!isset($_POST['id_menu'])) {
			$this->error(400, "Bad request");
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$id_menu = $_POST['id_menu'];
		$modelMenu = new Model_Menu();
		$modelMenu->id = $id_menu;
		$id_restaurant = $modelMenu->getRestaurant();
		$panier = $this->initPanier ($request, $id_restaurant);
		
		$quantite = $_POST['quantite'];
		$id_format = $_POST['id_format'];
		$id_formule = $_POST['id_formule'];
		$id_panier_menu = $panier->addMenu($id_menu, $id_format, $id_formule, $quantite);
		
		$categories = $modelMenu->getCategories($id_formule);
		foreach ($categories as $categorie) {
			if ($categorie['quantite'] == 1) {
				if (isset($_POST['contenu_'.$categorie['id']])) {
					$id_contenu = $_POST['contenu_'.$categorie['id']];
					$panier->addContenu($id_panier_menu, $id_contenu);
				}
			}
		}
	}
	
	public function removeMenu ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (!isset($_POST['id_panier'])) {
			$this->error(400, "Bad request");
		}
		if (!isset($_POST['id_panier_menu'])) {
			$this->error(400, "Bad request");
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->id = $_POST['id_panier'];
		$id_panier_menu = $_POST['id_panier_menu'];
		if (!$panier->removePanierMenu($id_panier_menu)) {
			$this->error(500, "Bad request");
		}
		$totalArticle = $panier->getNbArticle();
		if ($totalArticle == 0) {
			$panier->remove();
		}
	}
	
	public function commande ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		$rue = "";
		$ville = "";
		$code_postal = "";
		if (isset($_POST['rue'])) {
			
		}
		if (isset($_POST['ville'])) {
			
		}
		if (isset($_POST['code_postal'])) {
			
		}
		if (isset($_POST['telephone'])) {
			
		}
		$heure_commande = -1;
		$minute_commande = 0;
		if ((isset($_POST['type']) && $_POST['type'] == "pre_commande") || (!isset($_POST['type']) && isset($_POST['heure_commande'])) ) {
			$heure_commande = $_POST['heure_commande'];
			$minute_commande = $_POST['minute_commande'];
		}
		$rue = $_POST['rue'];
		$ville = $_POST['ville'];
		$code_postal = $_POST['code_postal'];
		$telephone = $_POST['telephone'];
		$request->disableLayout = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->validate($rue, $ville, $code_postal, $telephone, $heure_commande, $minute_commande);
	}
}