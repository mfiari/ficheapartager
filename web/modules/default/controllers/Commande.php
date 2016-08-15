<?php

include_once ROOT_PATH."function.php";

include_once ROOT_PATH."models/Template.php";
include_once ROOT_PATH."models/Commande.php";
include_once ROOT_PATH."models/Carte.php";
include_once ROOT_PATH."models/Supplement.php";
include_once ROOT_PATH."models/Menu.php";
include_once ROOT_PATH."models/Commande.php";
include_once ROOT_PATH."models/Restaurant.php";
include_once ROOT_PATH."models/Contenu.php";
include_once ROOT_PATH."models/Chat.php";
include_once ROOT_PATH."models/GCMPushMessage.php";
include_once ROOT_PATH."models/PDF.php";


class Controller_Commande extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "index" :
					$this->index($request);
					break;
				case "modified" :
					$this->modified($request);
					break;
				case "enCours" :
					$this->enCours($request);
					break;
				case "finish" :
					$this->finish($request);
					break;
				case "view" :
					$this->view($request);
					break;
				case "addCarte" :
					$this->addCarte($request);
					break;
				case "addMenu" :
					$this->addMenu($request);
					break;
				case "commande" :
					$this->commande($request);
					break;
				case "noter" :
					$this->noter($request);
					break;
				case "hasChat" :
					$this->hasChat($request);
					break;
				case "getChat" :
					$this->getChat($request);
					break;
				case "sendMessage" :
					$this->sendMessage($request);
					break;
				case "facture" :
					$this->facture($request);
					break;
			}
		} else {
			$this->index($request);
		}
	}
	
	public function index ($request) {
		if (isset($_GET["id"])) {
			$commande = new Model_Commande();
			$commande->uid = $request->_auth->id;
			$commande->id = $_GET["id"];
			$request->commande = $commande->load();
			$request->vue = $this->render("commande.php");
		} else {
			$commande = new Model_Commande();
			$commande->uid = $request->_auth->id;
			$request->commandes = $commande->loadNotFinishedCommande();
			$request->vue = $this->render("commandes.php");
		}
	}
	
	public function facture ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		
		$commande = new Model_Commande();
		$commande->uid = $request->_auth->id;
		$commande->id = $_GET["commande"];
		$commande->load();
		
		$pdf = new PDF ();
		$pdf->generateFactureClient($commande);
		$pdf->render();
	}
	
	public function modified ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		$commande = new Model_Commande();
		$commande->uid = $request->_auth->id;
		$list = $commande->getCommandeClientModified();
		if (count($list) != 0) {
			echo json_encode($list);
		}
	}
	
	public function enCours ($request) {
		$request->disableLayout = true;
		$commande = new Model_Commande();
		$commande->uid = $request->_auth->id;
		$request->commandes = $commande->loadNotFinishedCommande();
		$request->vue = $this->render("enCours.php");
	}
	
	public function finish ($request) {
		$commande = new Model_Commande();
		$commande->uid = $request->_auth->id;
		$request->commandes = $commande->loadFinishedCommande();
		$request->vue = $this->render("finishedCommandes.php");
	}
	
	public function view ($request) {
		$request->disableLayout = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$request->panier = $panier->load();
		$request->vue = $this->render("panier.php");
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
			$this->error(400, "Bad request");
		}
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->init();
		$id_carte = $_POST['id_carte'];
		$id_panier_carte = $panier->addCarte($id_carte);
		$modelCarte = new Model_Carte();
		$modelCarte->id = $id_carte;
		$carte = $modelCarte->getSupplements();
		foreach ($carte->supplements as $supplement) {
			var_dump($supplement);
			if (isset($_POST['check_supplement_'.$supplement->id])) {
				$panier->addCarteSupplement($id_panier_carte, $supplement->id);
			}
		}
		$request->disableLayout = true;
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
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->init();
		$id_menu = $_POST['id_menu'];
		/*$quantite = $_POST['quantite'];*/
		$quantite = 1;
		$id_format = $_POST['id_format'];
		$id_formule = $_POST['id_formule'];
		$id_panier_menu = $panier->addMenu($id_menu, $quantite, $id_format);
		
		$modelMenu = new Model_Menu();
		$modelMenu->id = $id_menu;
		$categories = $modelMenu->getCategories($id_formule);
		foreach ($categories as $categorie) {
			var_dump($categorie);
			if ($categorie['quantite'] == 1) {
				if (isset($_POST['contenu_'.$categorie['id']])) {
					$id_contenu = $_POST['contenu_'.$categorie['id']];
					$panier->addContenu($id_panier_menu, $id_contenu);
				}
			}
		}
		$request->disableLayout = true;
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
			$rue = $_POST['rue'];
		}
		if (isset($_POST['ville'])) {
			$ville = $_POST['ville'];
		}
		if (isset($_POST['code_postal'])) {
			$code_postal = $_POST['code_postal'];
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$panier = new Model_Panier();
		$panier->uid = $request->_auth->id;
		$panier->load();
		$commande = new Model_Commande();
		if ($commande->create($panier, $rue, $ville, $code_postal)) {
			$panier->remove();
		}
	}
	
	public function noter ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (isset($_POST['note'])) {
			$note = $_POST['note'];
		}
		if (isset($_POST['commentaire'])) {
			$commentaire = $_POST['commentaire'];
		}
		if (isset($_POST['id_commande'])) {
			$id_commande = $_POST['id_commande'];
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$commande = new Model_Commande();
		$commande->id = $id_commande;
		$commande->uid = $request->_auth->id;
		$commande->noter($note, $commentaire);
	}
	
	public function hasChat ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		$id_commande = $_GET['id_commande'];
		$chat = new Model_Chat();
		$chat->id_commande = $id_commande;
		echo $chat->hasChatClient();
	}
	
	public function getChat ($request) {
		$request->disableLayout = true;
		$id_commande = $_GET['id_commande'];
		$request->id_commande = $id_commande;
		$chat = new Model_Chat();
		$chat->id_commande = $id_commande;
		$request->messages = $chat->getChatCommande();
		$chat->vueClient();
		$commande = new Model_Commande();
		$commande->id = $id_commande;
		$request->livreur = $commande->getLivreur();
		$request->vue = $this->render("commande/chat.php");
	}
	
	public function sendMessage ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		$id_commande = $_POST['id_commande'];
		$message = $_POST['message'];
		$sender = "USER";
		$chat = new Model_Chat();
		$chat->id_commande = $id_commande;
		$chat->sender = $sender;
		$chat->message = $message;
		$chat->save();
		$commande = new Model_Commande();
		$commande->id = $id_commande;
		$livreur = $commande->getLivreur();
		$registatoin_ids = array($livreur->gcm_token);
		$gcm = new GCMPushMessage(GOOGLE_API_KEY);
		$message = "Vous avez un nouveau message";
		// listre des utilisateurs à notifier
		$gcm->setDevices($registatoin_ids);
		 
		// Le titre de la notification
		$data = array(
			"title" => "Nouveau message",
			"key" => "livreur-new-message",
			"id_commande" => $id_commande
		);
		 
		// On notifie nos utilisateurs
		$result = $gcm->send($message, $data);
	}
}