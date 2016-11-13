<?php

class Model_Public_Fiche extends Model_Template {
	
	private $id;
	private $titre;
	private $ordre;
	private $categorie;
	private $text;
	private $image;
	private $user;
	private $signaler;
	private $favorie;
	
	public function __construct($callParent = true) {
		if ($callParent) {
			parent::__construct();
		}
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
	
	public function save () {
		$sql = "INSERT INTO public_fiche (id_user, titre, categorie, text, url_image) 
		VALUES (:user, :titre, :categorie, :text, :url_image)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":user", $this->user->id);
		$stmt->bindValue(":titre", $this->titre);
		$stmt->bindValue(":categorie", $this->categorie);
		$stmt->bindValue(":text", $this->text);
		$stmt->bindValue(":url_image", $this->url_image);
		if (!$stmt->execute()) {
			var_dump($stmt->errorInfo());
			return false;
		}
		$this->id = $this->db->lastInsertId();
	}
	
	public function remove () {
		$sql = "DELETE FROM public_categorie WHERE id = :id";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $this->id);
		if (!$stmt->execute()) {
			var_dump($stmt->errorInfo());
			return false;
		}
		return true;
	}
	
	public function load () {
		$sql = "";
		if ($this->user) {
			$sql = "SELECT titre, text, url_image, (CASE WHEN ff.id IS NOT NULL THEN 1 ELSE 0 END) AS favorie
			FROM public_fiche pf
			LEFT JOIN fiche_favorie ff ON ff.id_fiche = pf.id AND ff.id_user = :user
			WHERE pf.id = :id";
		} else {
			$sql = "SELECT titre, text, url_image, 0 AS favorie FROM public_fiche WHERE id = :id";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":id", $this->id);
		if ($this->user) {
			$stmt->bindValue(":user", $this->user->id);
		}
		if (!$stmt->execute()) {
			var_dump($stmt->errorInfo());
			return false;
		}
		$value = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($value == null) {
			return;
		}
		$this->titre = $value['titre'];
		$this->text = $value['text'];
		$this->url_image = $value['url_image'];
		$this->favorie = $value['favorie'];
		return $this;
	}
	
	public function addToFavorie () {
		$sql = "INSERT INTO fiche_favorie (id_fiche, id_user) VALUES (:fiche, :user)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":fiche", $this->id);
		$stmt->bindValue(":user", $this->user->id);
		if (!$stmt->execute()) {
			var_dump($stmt->errorInfo());
			return false;
		}
		return true;
	}
	
	public function removeFavorie () {
		$sql = "DELETE FROM fiche_favorie WHERE id_fiche = :fiche AND id_user = :user";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":fiche", $this->id);
		$stmt->bindValue(":user", $this->user->id);
		if (!$stmt->execute()) {
			var_dump($stmt->errorInfo());
			return false;
		}
		return true;
	}
}