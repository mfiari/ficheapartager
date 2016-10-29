<?php

class Model_User extends Model_Template {
	
	private $id;
	private $nom;
	private $prenom;
	private $pseudo;
	private $email;
	private $password;
	private $compte;
	private $gcm_token;
	private $inscription_token;
	private $is_login;
	private $is_enable;
	
	public function __construct($callParent = true, $db = null) {
		if ($callParent) {
			parent::__construct($db);
		}
		$this->id = -1;
	}
	
	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}
	
	public function __set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
		return $this;
	}
	
	public function isEmailAvailable () {
		$sql = "SELECT uid FROM users WHERE email = :email";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":email", $this->email);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), LOG_LEVEL_ERROR, $sql);
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		return $value === false;
	}
	
	public function isPseudoAvailable () {
		$sql = "SELECT uid FROM users WHERE pseudo = :pseudo";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":pseudo", $this->pseudo);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), LOG_LEVEL_ERROR, $sql);
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		return $value === false;
	}
	
	public function get () {
		$sql = "SELECT nom, prenom, pseudo, email, compte, inscription_token, is_enable, date_creation FROM users WHERE uid = :uid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":uid", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), LOG_LEVEL_ERROR, $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->nom = $value['nom'];
		$this->prenom = $value['prenom'];
		$this->pseudo = $value['pseudo'];
		$this->email = $value['email'];
		$this->compte = $value['compte'];
		$this->inscription_token = $value['inscription_token'];
		$this->is_enable = $value['is_enable'];
		$this->date_creation = $value['date_creation'];
		return $this;
	}
	
	public function save () {
		if ($this->id == -1) {
			return $this->insert();
		}
		return false;
	}
	
	public function insert() {
		$sql = "INSERT INTO users (nom, prenom, pseudo, email, password, compte, inscription_token, is_enable, date_creation) 
		VALUES (:nom, :prenom, :pseudo, :email, sha1(:password), :compte, :token, false, now())";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":nom", $this->nom);
		$stmt->bindValue(":prenom", $this->prenom);
		$stmt->bindValue(":pseudo", $this->pseudo);
		$stmt->bindValue(":email", $this->email);
		$stmt->bindValue(":password", $this->password);
		$stmt->bindValue(":compte", $this->compte);
		$stmt->bindValue(":token", $this->inscription_token);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(),LOG_LEVEL_ERROR, $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$this->id = $this->db->lastInsertId();
		return true;
	}
	
	public function confirm () {
		$sql = "SELECT uid, nom, is_enable FROM users WHERE uid = :id AND inscription_token = :token";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $this->id);
		$stmt->bindValue(":token", $this->inscription_token);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), LOG_LEVEL_ERROR, $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($value == null || $value == false) {
			writeLog(SQL_LOG, $stmt->errorInfo(), LOG_LEVEL_WARNING, $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$sql = "UPDATE users SET is_enable = true WHERE uid = :uid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":uid", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : confirm", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		return true;
	}
	
	public function enable () {
		$sql = "UPDATE users SET is_enable = true WHERE uid = :uid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":uid", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : enable", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		return true;
	}
	
	public function disable () {
		$sql = "UPDATE users SET is_enable = false WHERE uid = :uid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":uid", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : disable", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		return true;
	}
	
	public function login($login, $password) {
		$sql = "SELECT uid, nom, compte, is_enable FROM users WHERE email = :email AND password = sha1(:password)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":email", $login);
		$stmt->bindValue(":password", $password);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : login", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($value == null || $value == false) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : login", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		
		$this->is_enable = $value["is_enable"];
		
		if ($this->is_enable) {
		
			$sql = "UPDATE users SET is_login = true WHERE uid = :uid";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":uid", $value["uid"]);
			if (!$stmt->execute()) {
				writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : login", $sql);
				$this->sqlHasFailed = true;
				return false;
			}
			
			$this->id = $value["uid"];
			$this->nom = $value["nom"];
			$this->login = $login;
			$this->compte = $value["compte"];
		}
		
		return $this;
	}
	
	public function logout () {
		$sql = "UPDATE users SET is_login = false WHERE uid = :id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : login", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		return true;
	}
	
	public function getById () {
		$sql = "SELECT nom, email, compte, gcm_token FROM users WHERE uid = :uid";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":uid", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : getById", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($value == null ||$value == false) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : getById", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		$this->nom = $value["nom"];
		$this->email = $value["email"];
		$this->compte = $value["compte"];
		$this->gcm_token = $value["gcm_token"];
		return $this;
	}
	
	public function registerToGcm () {
		$sql = "UPDATE users SET gcm_token = :token WHERE uid = :id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":token", $this->gcm_token);
		$stmt->bindValue(":id", $this->id);
		if (!$stmt->execute()) {
			writeLog(SQL_LOG, $stmt->errorInfo(), "Model_User : registerToGcm", $sql);
			$this->sqlHasFailed = true;
			return false;
		}
		return true;
	}
}