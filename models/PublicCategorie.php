<?php

class Model_Public_Categorie extends Model_Template {
	
	private $id;
	private $nom;
	private $ordre;
	private $parent_categorie;
	private $childrens;
	private $fiches;
	
	public function __construct($callParent = true) {
		if ($callParent) {
			parent::__construct();
		}
		$this->childrens = array();
		$this->fiches = array();
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
	
	public function addChildren ($categorie) {
		$this->childrens[] = $categorie;
	}
	
	public function addFiche ($fiche) {
		$this->fiches[] = $fiche;
	}
	
	public function save () {
		$sql = "INSERT INTO public_categorie (nom, parent, ordre) 
		VALUES (:nom, :parent, :ordre)";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":nom", $this->nom);
		$stmt->bindValue(":parent", $this->parent_categorie);
		$stmt->bindValue(":ordre", $this->ordre);
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
		$sql = "SELECT id, nom, parent, ordre FROM public_categorie WHERE parent = :parent ORDER BY ordre ASC, nom ASC";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":parent", $this->parent_categorie);
		if (!$stmt->execute()) {
			return false;
		}
		$categories = $stmt->fetchAll();
		$list = array();
		foreach ($categories as $c) {
			$categorie = new Model_Public_Categorie(false);
			$categorie->id = $c["id"];
			$categorie->nom = $c["nom"];
			$categorie->parent_categorie = $c["parent"];
			$categorie->ordre = $c["ordre"];
			$list[] = $categorie;
		}
		return $list;
	}
	
	public function loadFiches () {
		$sql = "SELECT id, titre FROM public_fiche WHERE categorie = :categorie ORDER BY titre ASC";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":categorie", $this->parent_categorie);
		if (!$stmt->execute()) {
			return false;
		}
		$fiches = $stmt->fetchAll();
		$list = array();
		foreach ($fiches as $f) {
			$fiche = new Model_Public_Fiche(false);
			$fiche->id = $f["id"];
			$fiche->titre = $f["titre"];
			$list[] = $fiche;
		}
		return $list;
	}
	
	public function getParentPath () {
		if ($this->parent_categorie != 0) {
			$sql = "SELECT id, nom, parent FROM public_categorie WHERE id = :id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(":id", $this->parent_categorie);
			if (!$stmt->execute()) {
				var_dump($stmt->errorInfo());
				return false;
			}
			$value = $stmt->fetch(PDO::FETCH_ASSOC);
			$categorie = new Model_Public_Categorie();
			$categorie->id = $value["id"];
			$categorie->nom = $value["nom"];
			$categorie->parent_categorie = $value["parent"];
			$parent = $categorie->getParentPath();
			if ($parent !== false) {
				$parent->addChildren($categorie);
				return $parent;
			} else {
				return $categorie;
			}
		}
		return false;
	}
}