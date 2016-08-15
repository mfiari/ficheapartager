<?php

include_once MODEL_PATH.'Template.php';
include_once MODEL_PATH.'User.php';

class Controller_User extends Controller_Template {
	
	public function handle() {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "view" :
					$this->getUserById();
					break;
				case "login" :
					$this->login();
					break;
				case "logout" :
					$this->logout();
					break;
				case "subscribe" :
					$this->subscribe();
					break;
				case "livreurReady" :
					$this->livreurReady();
					break;
				case "registerToGcm" :
					$this->registerToGcm();
					break;
				case "updateLivreurPosition" :
					$this->updateLivreurPosition();
					break;
			}
		} else {
			$this->getUserById();
		}
	}
	
	private function getUserById () {
		$model = new Model_User();
		if (isset($_GET["id"])) {
			$id = $_GET["id"];
			$result = $model->getUserById($id);
			if ($result) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row != null) {
					header("Content-type: text/xml; charset=utf-8");
					$dom = new DOMDocument();
					$user = $dom->createElement("user");
					$user->setAttribute("id", $uid);
					$user->setAttribute("email", $row['email']);
					$user->setAttribute("login", $row['login']);
					$user->setAttribute("compte", $row['compte']);
					$dom->appendChild($user);
					print $dom->saveXML();
				} else {
					$this->error(400, "User does not exist.");
				}
			} else {
				$this->error(500, "Request error.");
			}
		} else {
			$this->error(401, "Not authorized.");
		}
	}
	
	private function login () {
		if (!isset($_POST["login"]) || trim($_POST["login"]) == "") {
			$this->error(400, "Login non renseigné");
		}
		if (!isset($_POST["password"]) || trim($_POST["password"]) == "") {
			$this->error(400, "Mot de passe non renseigné");
		}
		$ext = $this->getExtension();
		$login = $_POST["login"];
		$password = $_POST["password"];
		$model = new Model_User();
		$user = $model->login($login, $password);
		if (!$user) {
			$this->error(404, "Login ou mot de passe incorrect");
		}
		if (!$user->is_enable) {
			$this->error(403, "Not authorized");
		}
		require 'vue/user/login.'.$ext.'.php';
	}
	
	private function logout () {
		if (!isset($_POST["id"]) || trim($_POST["id"]) == "") {
			$this->error(400, "Login non renseigné");
			return;
		}
		$id = $_POST["id"];
		$model = new Model_User();
		$model->id = $id;
		if (!$model->logout()) {
			$this->error(404, "Login ou mot de passe incorrect");
			return;
		}
	}
	
	private function subscribe () {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$errorMessage = array();
			if (!isset($_POST["name"]) || trim($_POST["name"]) == "") {
				$this->error(400, "Le nom ne peut être vide");
			}
			if (!isset($_POST["email"]) || trim($_POST["email"]) == "") {
				$this->error(400, "Le login ne peut être vide");
			}
			if (!isset($_POST["password"]) || trim($_POST["password"]) == "") {
				$this->error(400, "Le mot de passe ne peut être vide");
			}
			if (!isset($_POST["compte"]) || trim($_POST["compte"]) == "") {
				$this->error(400, "Le compte ne peut être vide");
			}
			$model = new Model_User();
			$model->nom = trim($_POST["name"]);
			$model->email = trim($_POST["email"]);
			$model->password = trim($_POST["password"]);
			$model->compte = $_POST["compte"];
			$model->inscription_token = generateToken();
			if ($model->isEmailAvailable()) {
				$model->beginTransaction();
				if ($model->save()) {
					$messageContent =  file_get_contents (ROOT_PATH.'mails/inscription.html');
					$messageContent = str_replace("[NOM]", $model->nom, $messageContent);
					$messageContent = str_replace("[PRENOM]", $model->prenom, $messageContent);
					$messageContent = str_replace("[UID]", $model->id, $messageContent);
					$messageContent = str_replace("[TOKEN]", $model->inscription_token, $messageContent);
					$messageContent = str_replace("[WEBSITE_URL]", WEBSITE_URL, $messageContent);
					
					send_mail ($model->email, "Création de votre compte", $messageContent);
				} else {
					$this->error(500, "Une erreur s'est produite, veuillez réessayé ultérieurement.");
				}
				$model->endTransaction();
			} else {
				$this->error(400, "Cet email existe déjà");
			}
		}
	}
	
	private function livreurReady () {
		if (!isset($_POST["id"])) {
			die();
		}
		$uid = $_POST["id"];
		$model = new Model_User();
		if (!$model->livreurReady($uid)) {
			return;
		}
	}
	
	private function livreurLogout () {
		if (!isset($_POST["id"])) {
			die();
		}
		$uid = $_POST["id"];
		$model = new Model_User();
		if (!$model->livreurLogout($uid)) {
			return;
		}
	}
	
	private function registerToGcm () {
		var_dump($_POST);
		echo "id : ";
		if (!isset($_POST["id"])) {
			$this->error(404, "Login ou mot de passe incorrect");
			return;
		}
		echo $_POST["id"];
		echo "gcm_token : ";
		if (!isset($_POST["gcm_token"])) {
			$this->error(404, "Login ou mot de passe incorrect");
			return;
		}
		echo $_POST["gcm_token"];
		$model = new Model_User();
		$model->id = $_POST["id"];
		$model->gcm_token = $_POST["gcm_token"];
		if (!$model->registerToGcm()) {
			return;
		}
	}
	
	private function updateLivreurPosition () {
		$id_livreur = $_POST['livreur'];
		$latitude = $_POST['latitude'];
		$longitude = $_POST['longitude'];
		$model = new Model_User();
		$model->id = $id_livreur;
		$model->latitude = $latitude;
		$model->longitude = $longitude;
		$model->updateLivreurPosition();
	}
}
