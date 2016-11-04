<?php

include_once MODEL_PATH."Template.php";
include_once MODEL_PATH."User.php";
include_once MODEL_PATH."PublicFiche.php";

class Controller_Fiche extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "index" :
					$this->index($request);
					break;
				case "groupe" :
					$this->groupe($request);
					break;
				case "fiche" :
					$this->fiche($request);
					break;
				case "view" :
					$this->view($request);
					break;
				case "addFavorie" :
					$this->addFavorie($request);
					break;
				case "removeFavorie" :
					$this->removeFavorie($request);
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
			$request->title = "Fiche";
			$request->vue = $this->render("fiche/favoris.php");
		} else {
			$this->redirect();
		}
	}
	
	public function groupe ($request) {
		if ($request->_auth) {
			$request->title = "Fiche";
			$request->vue = $this->render("fiche/groupe.php");
		} else {
			$this->redirect();
		}
	}
	
	public function fiche ($request) {
		if ($request->_auth) {
			$request->title = "Fiche";
			$request->vue = $this->render("fiche/fiche.php");
		} else {
			$this->redirect();
		}
	}
	
	public function view ($request) {
		if (isset($_GET['id'])) {
			$modelFiche = new Model_Public_Fiche();
			$modelFiche->id = $_GET['id'];
			$modelFiche->user = $request->_auth;
			$request->fiche = $modelFiche->load();
			$request->title = "Fiche";
			$request->vue = $this->render("fiche/view.php");
		} else {
			$this->redirect();
		}
	}
	
	public function addFavorie ($request) {
		if (!$request->_auth) {
			
		}
		if (!isset($_POST['id'])) {
			
		}
		$modelFiche = new Model_Public_Fiche();
		$modelFiche->id = $_POST['id'];
		$modelFiche->user = $request->_auth;
		if (!$modelFiche->addToFavorie()) {
			
		}
	}
	
	public function removeFavorie ($request) {
		if (!$request->_auth) {
			
		}
		if (!isset($_POST['id'])) {
			
		}
		$modelFiche = new Model_Public_Fiche();
		$modelFiche->id = $_POST['id'];
		$modelFiche->user = $request->_auth;
		if (!$modelFiche->removeFavorie()) {
			
		}
	}
}