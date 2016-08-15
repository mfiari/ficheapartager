<?php

include_once ROOT_PATH."function.php";

include_once MODEL_PATH.'Template.php';
include_once MODEL_PATH.'Restaurant.php';
include_once MODEL_PATH.'Horaire.php';
include_once MODEL_PATH.'Carte.php';
include_once MODEL_PATH.'Categorie.php';

class Controller_Restaurant extends Controller_Template {
	
	public function handle() {
		$this->init();
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "all" :
					$this->getAll();
					break;
				case "one" :
					$this->getById();
					break;
				case "search" :
					$this->recherche();
					break;
				case "categories" :
					$this->categories();
					break;
				case "categorie" :
					$this->categorie();
					break;
				case "carte" :
					$this->carte();
					break;
				case "menu" :
					$this->menu();
					break;
			}
		} else {
			$this->getAll();
		}
	}
	
	private function getAll () {
		$ext = $this->getExtension();
		$model = new Model_Restaurant();
		$result = $model->getAll();
		if ($result !== false) {
			foreach ($result as $restaurant) {
				$this->getLogo($restaurant);
			}
			require 'vue/restaurants_get.'.$ext.'.php';
		}
	}
	
	public function recherche () {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$ext = $this->getExtension();
			$filter = array();
			if (isset($_POST["distance"]) && $_POST["distance"] != "") {
				$filter["distanceKm"] = $_POST["distance"];
			} else {
				$filter["distanceKm"] = 5;
			}
			$distanceKm = $filter["distanceKm"];
			$modelRestaurant = new Model_Restaurant();
			$restaurants = $modelRestaurant->filter($filter);
			if (isset($_POST['adresse']) && trim($_POST['adresse']) != "") {
				$geocoder = "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";
				$localisation = urlencode($_POST['adresse']);
				$query = sprintf($geocoder,$localisation);
				$rd = json_decode(file_get_contents($query));
				if ($rd->{'status'} == "OK") {
					$addressComponents = $rd->{'results'}[0]->{'address_components'};
					$codePostal = "";
					$ville = "";
					$street_number = "";
					$route = "";
					for ($i = 0 ; $i < count($addressComponents) ; $i++) {
						if ($addressComponents[$i]->{'types'}[0] == 'postal_code') {
							$codePostal = $addressComponents[$i]->{'short_name'};
						} else if ($addressComponents[$i]->{'types'}[0] == 'locality') {
							$ville = $addressComponents[$i]->{'long_name'};
						} else if ($addressComponents[$i]->{'types'}[0] == 'street_number') {
							$street_number = $addressComponents[$i]->{'long_name'};
						} else if ($addressComponents[$i]->{'types'}[0] == 'route') {
							$route = $addressComponents[$i]->{'long_name'};
						}
					}
					$coord = $rd->{'results'}[0]->{'geometry'}->{'location'};
					$user_latitude = $coord->{'lat'};
					$user_longitude = $coord->{'lng'};
					
				}
			} else if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
				$user_latitude = $_POST['latitude'];
				$user_longitude = $_POST['longitude'];
				$adresseUser = $user_latitude.','.$user_longitude;
			} else {
				$this->error(400, "bad request");
			}
			$availableRestaurant = array();
			$modelUser = new Model_User();
			foreach ($restaurants as $restaurant) {
				$adresseResto = $restaurant->latitude.','.$restaurant->longitude;
				$result = getDistance($adresseUser, $adresseResto);
				if ($result['status'] == "OK") {
					$distanceRestoKm = $result['distance'] / 1000;
					if ($distanceRestoKm < $distanceKm) {
						$restaurant->distance = $distanceRestoKm;
						$availableRestaurant[] = $restaurant;
					}
				}
				$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
				$restaurant->has_livreur_dispo = count($livreurs) > 0;
			}
			$restaurants = $availableRestaurant;
				
			foreach ($restaurants as $restaurant) {
				$this->getLogo($restaurant);
			}
			
			$result = $restaurants;
			require 'vue/restaurants_get.'.$ext.'.php';
		}
		
		if ($rd->{'status'} == "OK") {
			$addressComponents = $rd->{'results'}[0]->{'address_components'};
			$codePostal = "";
			$ville = "";
			$street_number = "";
			$route = "";
			for ($i = 0 ; $i < count($addressComponents) ; $i++) {
				if ($addressComponents[$i]->{'types'}[0] == 'postal_code') {
					$codePostal = $addressComponents[$i]->{'short_name'};
				} else if ($addressComponents[$i]->{'types'}[0] == 'locality') {
					$ville = $addressComponents[$i]->{'long_name'};
				} else if ($addressComponents[$i]->{'types'}[0] == 'street_number') {
					$street_number = $addressComponents[$i]->{'long_name'};
				} else if ($addressComponents[$i]->{'types'}[0] == 'route') {
					$route = $addressComponents[$i]->{'long_name'};
				}
			}
			$_SESSION['search_adresse'] = $request->search_ardresse;
			$_SESSION['search_ville'] = $ville;
			$_SESSION['search_cp'] = $codePostal;
			$_SESSION['search_rue'] = $street_number.' '.$route;
			$coord = $rd->{'results'}[0]->{'geometry'}->{'location'};
			$user_latitude = $coord->{'lat'};
			$user_longitude = $coord->{'lng'};
			$_SESSION['search_latitude'] = $user_latitude;
			$_SESSION['search_longitude'] = $user_longitude;
			$availableRestaurant = array();
			$adresseUser = $user_latitude.','.$user_longitude;
			
		} else {
			$request->adressError = true;
		}
		$request->ouvert = true;
		$request->distance = $distanceKm;
		$request->ville = $city;
		$request->villes = array_unique(array_object_column($restaurants, 'ville'));
		$request->restaurants = $restaurants;
		$request->vue = $this->render("restaurants.php");
		
		
	}
	
	private function getById () {
		if (isset($_GET["id"])) {
			$id = $_GET["id"];
			$ext = $this->getExtension();
			$model = new Model_Restaurant();
			$model->id = $id;
			$result = $model->load();
			if ($result !== false) {
				require 'vue/restaurant_get.'.$ext.'.php';
			}
		}
	}
	
	private function categories () {
		if (isset($_GET['id'])) {
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$restaurant = $modelRestaurant->load();
			$imgSize = "default";
			if (isset($_GET['img_size'])) {
				$imgSize = $_GET['img_size'];
			}
			$modelCategorie = new Model_Categorie();
			$restaurant->categories = $modelCategorie->getParentContenu($restaurant->id, $imgSize);
			require 'vue/restaurant/restaurant.'.$this->ext.'.php';
		}
	}
	
	private function categorie () {
		$id_categorie = $_GET["id_categorie"];
		$id_restaurant = $_GET["id_restaurant"];
		$modelRestaurant = new Model_Restaurant();
		$modelRestaurant->id = $_GET['id_restaurant'];
		$restaurant = $modelRestaurant->loadMinInformation();
		$imgSize = "default";
		if (isset($_GET['img_size'])) {
			$imgSize = $_GET['img_size'];
		}
		$modelCategorie = new Model_Categorie();
		$modelCategorie->id = $_GET['id_categorie'];
		$categorie = $modelCategorie->load();
		$childrens = $modelCategorie->getChildren();
		foreach ($childrens as $children) {
			$children->loadContenu($id_restaurant, $imgSize);
			$restaurant->addCategorie($children);
		}
		require 'vue/restaurant/categorie.'.$this->ext.'.php';
	}
	
	private function carteContenu () {
		$modelCarte = new Model_Carte();
		$modelCarte->id = $_GET['id'];
		$carte = $modelCarte->load();
		require 'vue/carte/detail.'.$this->ext.'.php';
	}
	
	private function carte () {
		if (isset($_GET["id_carte"])) {
			
			$id_restaurant = $_GET['id_restaurant'];
			
			$modelCarte = new Model_Carte();
			$modelCarte->id = $_GET['id_carte'];
			$carte = $modelCarte->load();
			$carte->getLogo($id_restaurant);
			
			/*$modelUser = new Model_User();
			$ville = $_SESSION['search_ville'];
			$codePostal = $_SESSION['search_cp'];
			$restaurant = new Model_Restaurant();
			$restaurant->id = $request->id_restaurant;
			$fields = array ("code_postal", "ville");
			$restaurant->get($fields);
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$request->has_livreur_dispo = count($livreurs) > 0;*/
			
			require 'vue/restaurant/carte.'.$this->ext.'.php';
		}
	}
	
	private function menu () {
		$restaurant = new Model_Restaurant();
		$restaurant->id = $_GET['id'];
		$fields = array ("nom", "code_postal", "ville");
		$restaurant->get($fields);
		
		$modelUser = new Model_User();
		
		if (isset($_GET['code_postal']) && isset($_GET['ville'])) {
			$ville = $_GET['ville'];
			$codePostal = $_GET['code_postal'];
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$has_livreur_dispo = count($livreurs) > 0;
		} else if (isset($_GET['latitude']) && isset($_GET['longitude'])) {
			$latitude = $_GET['latitude'];
			$longitude = $_GET['longitude'];
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$has_livreur_dispo = count($livreurs) > 0;
		} else if (isset($_SESSION['search_cp']) && isset($_SESSION['search_ville'])) {
			$ville = $_SESSION['search_ville'];
			$codePostal = $_SESSION['search_cp'];
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$has_livreur_dispo = count($livreurs) > 0;
		} else {
			$has_livreur_dispo = false;
		}
		
		$modelMenu = new Model_Menu();
		$modelMenu->id = $_GET['id_menu'];
		$menu = $modelMenu->load($restaurant->id);
		
		require 'vue/restaurant/menu.'.$this->ext.'.php';
	}
	
	private function getLogo ($restaurant) {
		$imgPath = "res/img/restaurant/";
		$logoDirectory = WEBSITE_PATH.$imgPath;
		if (file_exists($logoDirectory.$restaurant->id)) {
			if (file_exists($logoDirectory.$restaurant->id.'/logo.png')) {
				$restaurant->logo = $imgPath.$restaurant->id.'/logo.png';
			} else if (file_exists($logoDirectory.$restaurant->id.'/logo.jpg')) {
				$restaurant->logo = $imgPath.$restaurant->id.'/logo.jpg';
			} else if (file_exists($logoDirectory.$restaurant->id.'/logo.gif')) {
				$restaurant->logo = $imgPath.$restaurant->id.'/logo.gif';
			} else {
				$restaurant->logo = $imgPath.'default/logo.jpg';
			}
		} else {
			$restaurant->logo = $imgPath.'default/logo.jpg';
		}
	}
}
