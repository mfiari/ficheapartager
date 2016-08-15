<?php

include_once ROOT_PATH."function.php";

include_once MODEL_PATH.'Template.php';
include_once MODEL_PATH.'Panier.php';
include_once MODEL_PATH.'Restaurant.php';
include_once MODEL_PATH.'Carte.php';
include_once MODEL_PATH."Format.php";
include_once MODEL_PATH."Supplement.php";
include_once MODEL_PATH."Option.php";
include_once MODEL_PATH."OptionValue.php";
include_once MODEL_PATH."Accompagnement.php";

class Controller_Panier extends Controller_Template {
	
	public function handle() {
		$this->init();
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "all" :
					$this->getAll();
					break;
				case "getByUser" :
					$this->getByUser();
					break;
				case "ajouter" :
					$this->ajouter();
					break;
				case "commande" :
					$this->commande();
					break;
				case "remove" :
					$this->remove();
					break;
				case "view" :
					$this->view();
					break;
				case "addCarte" :
					$this->addCarte();
					break;
			}
		} else {
			$this->getAll();
		}
	}
	
	private function getAll () {
		
	}
	
	private function getByUser () {
		if (isset($_GET["id_user"])) {
			$id_user = $_GET["id_user"];
			$ext = $this->getExtension();
			$model = new Model_Panier();
			$result = $model->getByUser($id_user);
			if ($result !== false) {
				require 'vue/panier_get.'.$ext.'.php';
			}
		}
	}
	
	private function ajouter () {
		if (!isset($_POST["id_menu"])) {
			die();
		}
		if (!isset($_POST["id_user"])) {
			die();
		}
		if (!isset($_POST["quantite"])) {
			die();
		}
		$id_menu = $_POST["id_menu"];
		$id_user = $_POST["id_user"];
		$quantite = $_POST["quantite"];
		$modelPanier = new Model_Panier();
		$id_panier = $modelPanier->create($id_user);
		$id_panier_menu = $modelPanier->addMenu($id_panier, $id_menu, $quantite);
		$modelContenu = new Model_Contenu();
		$result = $modelContenu->getCategorieByMenu($id_menu);
		foreach ($result as $key => $value) {
			$id_categorie = $value["id_categorie"];
			$id_contenu = $value["id"];
			$quantite = $value["quantite"];
			if (isset($_POST[$id_categorie."_".$id_contenu])) {
				$nbEnregistrement = 1;
				if ($quantite > 1) {
					$nbEnregistrement = $_POST["select".$id_categorie."_".$id_contenu];
				}
				for ($i = 0 ; $i < $nbEnregistrement ; $i++) {
					$modelPanier->create($id_panier_menu, $id_contenu);
				}
			}
		}
	}
	
	private function commande () {
		if (!isset($_POST["id_user"])) {
			$this->error(400, "Bad request");
		}
		$panier = new Model_Panier();
		$panier->uid = $_POST["id_user"];
		$panier->init();
		$commande = new Model_Commande();
		if ($commande->create($panier)) {
			$panier->remove();
			$user = new Model_User();
			
			$restaurantUsers = $user->getRestaurantUsers($panier->id_restaurant);
			if (count($restaurantUsers) > 0) {
				$registatoin_ids = array();
				$gcm = new GCMPushMessage(GOOGLE_API_KEY);
				foreach ($restaurantUsers as $restaurantUser) {
					array_push($registatoin_ids, $restaurantUser->gcm_token);
				}
				$message = "Vous avez reçu une nouvelle commande";
				// listre des utilisateurs à notifier
				$gcm->setDevices($registatoin_ids);
			 
				// Le titre de la notification
				$data = array(
					"title" => "Nouvelle commande",
					"key" => "restaurant-new-commande",
					"id_commande" => $commande->id
				);
			 
				// On notifie nos utilisateurs
				$result = $gcm->send($message, $data);
				//$result = $gcm->send('/topics/restaurant-commande',$message, $data);
			}
			
			$messageContent =  file_get_contents (ROOT_PATH.'mails/nouvelle_commande_admin.html');
			
			$messageContent = str_replace("[COMMANDE_ID]", $commande->id, $messageContent);
			
			send_mail ("admin@homemenus.fr", "Nouvelle commande", $messageContent);
		}
	}
	
	private function remove () {
		if (!isset($_POST["id_menu"])) {
			die();
		}
		if (!isset($_POST["id_user"])) {
			die();
		}
		$id_menu = $_POST["id_menu"];
		$id_user = $_POST["id_user"];
		$modelPanier = new Model_Panier();
		$modelPanier->remove($id_user, $id_menu);
	}
	
	private function initPanier ($request, $id_user, $id_restaurant) {
		$panier = new Model_Panier();
		$panier->uid = $id_user;
		$panier->init();
		if ($panier->id_restaurant == -1) {
			$panier->setRestaurant($id_restaurant);
		} else if ($panier->id_restaurant != $id_restaurant) {
			$this->error(400, "Bad request");
		}
		return $panier;
	}
	
	private function view () {
		$panier = new Model_Panier();
		$panier->uid = $_GET['id_user'];
		$panier->load();
		require 'vue/panier/panier.'.$this->ext.'.php';
	}
	
	public function addCarte ($request) {
		if ($_SERVER['REQUEST_METHOD'] != "POST") {
			$this->error(405, "Method not allowed");
		}
		if (!isset($_POST['id_carte'])) {
			$this->error(409, "Conflict");
		}
		$id_restaurant = $_POST['id_restaurant'];
		$id_user = $_POST['id_user'];
		$panier = $this->initPanier ($request, $id_user, $id_restaurant);
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
}
