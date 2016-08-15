<?php

include_once MODEL_PATH.'Template.php';
include_once MODEL_PATH.'Menu.php';
/*include_once MODEL_PATH.'Categorie.php';
include_once MODEL_PATH.'Contenu.php';*/

class Controller_Menu extends Controller_Template {
	
	public function handle() {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "getByRestaurant" :
					$this->getByRestaurant();
					break;
				case "one" :
					$this->getById();
					break;
			}
		} else {
			$this->getAll();
		}
	}
	
	private function getAll () {
		
	}
	
	private function getById () {
		if (isset($_GET["id"])) {
			$id = $_GET["id"];
			$ext = $this->getExtension();
			$modelMenu = new Model_Menu();
			$result = $modelMenu->getById($id);
			$menu = array();
			$menu["id"] = $result["id"];
			$menu["nom"] = utf8_encode($result["nom"]);
			$menu["prix"] = $result["prix"];
			$menu["preparation"] = $result["temps_preparation"];
			$menu["commentaire"] = utf8_encode($result["commentaire"]);
			$menu["categories"] = array();
			$modelCategorie = new Model_Categorie();
			$result = $modelCategorie->getByMenu($id);
			foreach ($result as $key => $value) {
				$id_categorie = $value["id_categorie"];
				$menu["categories"][$id_categorie] = array();
				$menu["categories"][$id_categorie]["id"] = $value["id"];
				$menu["categories"][$id_categorie]["nom"] = utf8_encode($value["nom"]);
				$menu["categories"][$id_categorie]["quantite"] = $value["quantite"];
				$menu["categories"][$id_categorie]["contenus"] = array();
			}
			$modelContenu = new Model_Contenu();
			$result = $modelContenu->getByMenu($id);
			foreach ($result as $key => $value) {
				$id_categorie = $value["id_categorie"];
				$menu["categories"][$id_categorie]["contenus"][] = array();
				$indice = count($menu["categories"][$id_categorie]["contenus"])-1;
				$menu["categories"][$id_categorie]["contenus"][$indice]["id"] = $value["id"];
				$menu["categories"][$id_categorie]["contenus"][$indice]["nom"] = utf8_encode($value["nom"]);
				$menu["categories"][$id_categorie]["contenus"][$indice]["obligatoire"] = $value["obligatoire"];
				$menu["categories"][$id_categorie]["contenus"][$indice]["commentaire"] = utf8_encode($value["commentaire"]);
			}
			require 'vue/menu_get.'.$ext.'.php';
		}
	}
	
	private function getByRestaurant () {
		if (isset($_GET["id_restaurant"])) {
			$id_restaurant = $_GET["id_restaurant"];
			$ext = $this->getExtension();
			$model = new Model_Menu();
			$result = $model->getByRestaurant($id_restaurant);
			if ($result !== false) {
				require 'vue/menus_get.'.$ext.'.php';
			}
		}
	}
}
