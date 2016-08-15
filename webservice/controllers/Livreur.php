<?php

class Controller_Livreur extends Controller_Template {
	
	public function handle() {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "all" :
					$this->getAllLivreur();
					break;
			}
		} else {
			$this->getAllLivreur();
		}
	}
	
	private function getAllLivreur () {
		
	}
}
