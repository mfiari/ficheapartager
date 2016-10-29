<?php

include_once MODEL_PATH."Template.php";
include_once MODEL_PATH."User.php";

class Controller_Compte extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "index" :
					$this->index($request);
					break;
				case "subscribe" :
					$this->subscribe($request);
					break;
				case "paiement" :
					$this->paiement($request);
					break;
				case "activation" :
					$this->activation($request);
					break;
				case "forgot_password" :
					$this->forgot_password($request);
					break;
				case "reset_password" :
					$this->reset_password($request);
					break;
				default :
					$this->redirect('404');
					break;
			}
		} else {
			$this->index($request);
		}
	}
	
	public function index ($request) {
		if ($request->_auth) {
			$request->title = "Compte";
			$modelUser = new Model_User();
			$modelUser->id = $request->_auth->id;
			$request->user = $modelUser->getById();
			$request->vue = $this->render("compte.php");
		} else {
			$this->redirect('inscription');
		}
	}
	
	public function subscribe ($request) {
		if ($request->request_method == "POST") {
			$password = $_POST['password'];
			$confirm_password = $_POST['confirm_password'];
			$compte = $_POST['compte'];
			$errorMessage = array();
			if (!isset($_POST["name"]) || trim($_POST["name"]) == "") {
				$errorMessage["EMPTY_NOM"] = "Le nom ne peut être vide";
			} else {
				$request->fieldNom = $_POST["name"];
			}
			if (!isset($_POST["prenom"]) || trim($_POST["prenom"]) == "") {
				$errorMessage["EMPTY_PRENOM"] = "Le prénom ne peut être vide";
			} else {
				$request->fieldPrenom = $_POST["prenom"];
			}
			if (!isset($_POST["pseudo"]) || trim($_POST["pseudo"]) == "") {
				$errorMessage["EMPTY_PSEUDO"] = "Le pseudo ne peut être vide";
			} else {
				$request->fieldPseudo = $_POST["pseudo"];
			}
			if (!isset($_POST["email"]) || trim($_POST["email"]) == "") {
				$errorMessage["EMPTY_EMAIL"] = "L'email ne peut être vide";
			} else {
				$request->fieldEmail = $_POST["email"];
			}
			if (!isset($_POST["password"]) || trim($_POST["password"]) == "") {
				$errorMessage["EMPTY_PASSWORD"] = "Le mot de passe ne peut être vide";
			}
			if (!isset($_POST["confirm_password"]) || trim($_POST["confirm_password"]) == "") {
				$errorMessage["EMPTY_CONFIRM_PASSWORD"] = "Le mot de passe ne peut être vide";
			}
			if (isset($_POST["password"]) && isset($_POST["confirm_password"]) && $_POST["password"] != $_POST["confirm_password"]) {
				$errorMessage["DIFFERENT_PASSWORD"] = "Les mot de passe saisie doit être identique";
			}
			if (!isset($_POST["compte"]) || trim($_POST["compte"]) == "") {
				$errorMessage["EMPTY_COMPTE"] = "Le compte ne peut être vide";
			} else {
				$compte = $_POST["compte"];
			}
			if (count($errorMessage) == 0) {
				$model = new Model_User();
				$model->nom = trim($_POST["name"]);
				$model->prenom = trim($_POST["prenom"]);
				$model->pseudo = trim($_POST["pseudo"]);
				$model->email = trim($_POST["email"]);
				$model->password = trim($_POST["password"]);
				$model->inscription_token = generateToken();
				switch ($compte) {
					case 'CLASSIQUE' : 
						$model->compte = USER_CLASSIQUE;
						break;
					case 'PRO' : 
						$model->compte = USER_PRO;
						break;
					case 'PREMIUM' : 
						$model->compte = USER_PREMIUM;
						break;
				}
				if ($model->isPseudoAvailable()) {
					if ($model->isEmailAvailable()) {
						if ($model->save()) {
							$_SESSION['uid'] = $model->id;
							if ($model->status == USER_CLASSIQUE) {
								$this->redirect('confirmation', 'compte');
							} else {
								$this->redirect('paiement', 'compte');
							}
						} else {
							$request->errorMessage = array("CREATE_ERROR" => "Une erreur s'est produite, veuillez réessayé ultérieurement.");
						}
					} else {
						$request->errorMessage = array("USER_EXISTS" => "Cet email existe déjà");
					}
				} else {
					$request->errorMessage = array("USER_EXISTS" => "Ce pseudo existe déjà");
				}
			} else {
				$request->errorMessage = $errorMessage;
			}
		}
		$request->vue = $this->render("compte/subscribe.php");
	}
	
	public function paiement ($request) {
		$modelUser = new Model_User();
		//$modelUser->id = $_SESSION['uid'];
		$modelUser->id = 3;
		$request->user = $modelUser->get();
		if ($modelUser->compte == USER_PREMIUM) {
			$request->prix = 5;
		} else if ($modelUser->compte == USER_PRO) {
			$request->prix = 2;
		}
		$request->vue = $this->render("compte/paiement.php");
	}
	
	public function validation () {
		$messageContent =  file_get_contents (ROOT_PATH.'mails/inscription.html');
		$messageContent = str_replace("[NOM]", $model->nom, $messageContent);
		$messageContent = str_replace("[PRENOM]", $model->prenom, $messageContent);
		$messageContent = str_replace("[UID]", $model->id, $messageContent);
		$messageContent = str_replace("[TOKEN]", $model->inscription_token, $messageContent);
		$messageContent = str_replace("[WEBSITE_URL]", WEBSITE_URL, $messageContent);
						
		send_mail ($model->email, "Création de votre compte", $messageContent);
		$this->redirect("inscription_sucess");
	}
	
	public function activation ($request) {
		$model = new Model_User();
		$model->id = trim($_GET["uid"]);
		$model->inscription_token = trim($_GET["token"]);
		if ($model->confirm()) {
			$model->getById();
			$messageContent =  file_get_contents (ROOT_PATH.'mails/inscription_admin.html');
					
			$messageContent = str_replace("[PRENOM]", $model->prenom, $messageContent);
			$messageContent = str_replace("[NOM]", $model->nom, $messageContent);
			if ($model->ville != '') {
				$messageContent = str_replace("[ADRESSE]", $model->ville.' ('.$model->code_postal.')', $messageContent);
			} else {
				$messageContent = str_replace("[ADRESSE]", "(adresse non renseignée)", $messageContent);
			}
			send_mail ("admin@homemenus.fr", "création de compte", $messageContent);
			
			$_SESSION["uid"] = $model->id;
			$_SESSION["session"] = $model->session;
			$request->vue = $this->render("confirmation_inscription_success.php");
		} else {
			$request->vue = $this->render("confirmation_inscription_fail.php");
		}
	}
	
	public function forgot_password ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
		}
		if (!isset($_POST['login'])) {
			$this->error(400, "bad request");
		}
		$model = new Model_User();
		$model->login = trim($_POST["login"]);
		$user = $model->getByLogin();
		if ($user) {
			if (!$user->is_enable) {
				$this->error(403, "Not authorized");
			}
			$token = generateToken();
			$messageContent =  file_get_contents (ROOT_PATH.'mails/forgot_password.html');
			$messageContent = str_replace("[UID]", $model->id, $messageContent);
			$messageContent = str_replace("[TOKEN]", $token, $messageContent);
			$messageContent = str_replace("[WEBSITE_URL]", WEBSITE_URL, $messageContent);
					
			send_mail ($model->email, "Changement de mot de passe", $messageContent);
			
		} else {
			$this->error(404, "Not found");
		}
	}
	
	public function reset_password ($request) {
		if ($request->request_method == "POST") {
			$model = new Model_User();
			$model->id = trim($_POST["uid"]);
			$model->password = trim($_POST["password"]);
			$model->changePassword();
		} else if ($request->request_method == "GET") {
			$request->vue = $this->render("reset_password.php");
		}
	}
}