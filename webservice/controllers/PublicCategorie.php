<?php

include_once MODEL_PATH.'Template.php';
include_once MODEL_PATH.'PublicCategorie.php';

class Controller_Public_Categorie extends Controller_Template {
	
	public function handle() {
		$this->init();
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "list" :
					$this->getList();
					break;
				case "view" :
					$this->view();
					break;
			}
		}
	}
	
	private function getList () {
		$parent = 0;
		if (isset($_GET["parent"])) {
			$parent = $_GET["parent"];
		}
		$categorie = new Model_Public_Categorie();
		$categorie->parent_categorie = $parent;
		$list = $categorie->load();
		$path = $categorie->getParentPath();
		$fiches = $categorie->loadFiches();
		require 'vue/categorie/public/list.'.$this->ext.'.php';
	}
	
	private function view () {
		require 'vue/categorie/public/view.'.$this->ext.'.php';
	}
}