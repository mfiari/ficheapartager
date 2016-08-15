<?php

include_once ROOT_PATH."function.php";

include_once ROOT_PATH."models/Template.php";
include_once ROOT_PATH."models/User.php";
include_once ROOT_PATH."models/Restaurant.php";
include_once ROOT_PATH."models/Horaire.php";
include_once ROOT_PATH."models/Carte.php";
include_once ROOT_PATH."models/Format.php";
include_once ROOT_PATH."models/Supplement.php";
include_once ROOT_PATH."models/Menu.php";
include_once ROOT_PATH."models/Formule.php";
include_once ROOT_PATH."models/Categorie.php";
include_once ROOT_PATH."models/Contenu.php";
include_once ROOT_PATH."models/Tag.php";
include_once ROOT_PATH."models/Option.php";
include_once ROOT_PATH."models/OptionValue.php";
include_once ROOT_PATH."models/Accompagnement.php";

class Controller_Restaurant extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "index" :
					$this->index($request);
					break;
				case "recherche" :
					$this->recherche($request);
					break;
				case "categories" :
					$this->categories($request);
					break;
				case "view" :
					$this->view($request);
					break;
				case "carte" :
					$this->carte($request);
					break;
				case "menu" :
					$this->menu($request);
					break;
				case "autocompleteTag" :
					$this->autocompleteTag($request);
					break;
				case "autocompleteRestaurant" :
					$this->autocompleteRestaurant($request);
					break;
				default :
					$this->redirect('404');
					break;
			}
		} else {
			$this->redirect('404');
		}
	}
	
	public function index ($request) {
		if (isset($_GET['id'])) {
			$request->title = "Restaurant";
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$request->restaurant = $modelRestaurant->load();
			$modelCategorie = new Model_Categorie();
			$request->restaurant->categories = $modelCategorie->getParentContenu($request->restaurant->id);
			$request->search_adresse = $_SESSION['search_adresse'];
			$request->vue = $this->render("restaurant.php");
		} else {
			$this->redirect();
		}		
	}
	
	public function recherche ($request) {
		$city = "";
		if ($request->request_method == "GET") {
			if (!isset($_SESSION['search_serialized'])) {
				$this->redirect();
			}
			$filter = unserialize($_SESSION['search_serialized']);
		} else if ($request->request_method == "POST") {
			if (!isset($_POST['adresse']) || trim($_POST['adresse']) == "") {
				$this->redirect();
			}
			$filter = array();
			$filter["search_ardresse"] = $_POST['adresse'];
			if (isset($_POST["city"]) && $_POST["city"] != "") {
				$filter["ville"] = $_POST["city"];
			}
			if (isset($_POST["groupe"]) && $_POST["groupe"] != "") {
				$filter["groupe"] = $_POST["groupe"];
			}
			if (isset($_POST["distance"]) && $_POST["distance"] != "") {
				$filter["distanceKm"] = $_POST["distance"];
			} else {
				$filter["distanceKm"] = 5;
			}
			$modelRestaurant = new Model_Restaurant();
		
			$tags = $modelRestaurant->getTags();
			
			$tagsFilter = array();
			foreach ($tags as $tag) {
				if (isset($_POST["tag_".$tag->id])) {
					$tagsFilter[] = $tag->id;
				}
			}
			$filter["tags"] = $tags;
			$filter["tagsFilter"] = $tagsFilter;
			$_SESSION['search_serialized'] = serialize($filter);
		} else {
			$this->redirect();
		}
		
		if (isset($filter["ville"])) {
			$city = $filter["ville"];
		}
		$distanceKm = $filter["distanceKm"];
		
		$modelRestaurant = new Model_Restaurant();
		
		$request->tags = $filter["tags"];
		$request->tagsFilter = $filter["tagsFilter"];
		
		$restaurants = $modelRestaurant->filter($filter);
		
		$request->search_ardresse = $filter["search_ardresse"];
		$geocoder = "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";
		$localisation = urlencode($filter["search_ardresse"]);
		$query = sprintf($geocoder,$localisation);
		$rd = json_decode(file_get_contents($query));
		/*var_dump($rd);
		var_dump($rd->{'status'}); die();*/
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
			$modelUser = new Model_User();
			foreach ($restaurants as $restaurant) {
				if ($restaurant->latitude != 0 && $restaurant->longitude != 0) {
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
			}
			$restaurants = $availableRestaurant;
				
			foreach ($restaurants as $restaurant) {
				$this->getLogo($restaurant);
			}
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
	
	private function view ($request) {
		
	}
	
	private function categories ($request) {
		$id_categorie = $_GET["id_categorie"];
		$id_restaurant = $_GET["id_restaurant"];
		$modelRestaurant = new Model_Restaurant();
		$modelRestaurant->id = $_GET['id_restaurant'];
		$restaurant = $modelRestaurant->loadMinInformation();
		$modelCategorie = new Model_Categorie();
		$modelCategorie->id = $_GET['id_categorie'];
		$childrens = $modelCategorie->getChildren();
		$modelCategorie->loadContenu($id_restaurant);
		$restaurant->addCategorie($modelCategorie);
		foreach ($childrens as $children) {
			$children->loadContenu($id_restaurant);
			$restaurant->addCategorie($children);
		}
		$request->restaurant = $restaurant;
		$request->vue = $this->render("categories.php");
	}
	
	private function carte ($request) {
		if (isset($_GET["id_carte"])) {
			$request->disableLayout = true;
			$modelCarte = new Model_Carte();
			$modelCarte->id = $_GET['id_carte'];
			$request->id_restaurant = $_GET['id'];
			$request->carte = $modelCarte->load();
			$request->carte->getLogo($request->id_restaurant);
			
			$modelUser = new Model_User();
			$ville = $_SESSION['search_ville'];
			$codePostal = $_SESSION['search_cp'];
			$restaurant = new Model_Restaurant();
			$restaurant->id = $request->id_restaurant;
			$fields = array ("code_postal", "ville");
			$restaurant->get($fields);
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$request->has_livreur_dispo = count($livreurs) > 0;
			
			$request->vue = $this->render("carteDetail.php");
		} else {
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$request->restaurant = $modelRestaurant->loadCarte();
			$request->vue = $this->render("carte.php");
		}
	}
	
	private function menu ($request) {
		if (isset($_GET["id_menu"])) {
			$request->disableLayout = true;
			$request->id_restaurant = $_GET['id'];
			$modelMenu = new Model_Menu();
			$modelMenu->id = $_GET['id_menu'];
			$request->menu = $modelMenu->load();
			
			$modelUser = new Model_User();
			$ville = $_SESSION['search_ville'];
			$codePostal = $_SESSION['search_cp'];
			$restaurant = new Model_Restaurant();
			$restaurant->id = $request->id_restaurant;
			$fields = array ("code_postal", "ville");
			$restaurant->get($fields);
			$livreurs = $modelUser->getLivreurAvailableForRestaurant($codePostal, $ville, $restaurant);
			$request->has_livreur_dispo = count($livreurs) > 0;
			
			$request->vue = $this->render("menu.php");
		} else {
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$restaurant = $modelRestaurant->loadMinInformation();
			$restaurant->loadMenus();
			$request->restaurant = $restaurant;
			$request->javascripts = array("res/js/menu.js");
			$request->vue = $this->render("menus.php");
		}
	}
	
	private function autocompleteTag ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		if(isset($_GET['term'])) {
			$restaurants = $_GET['restaurant'];
			// Mot tapé par l'utilisateur
			$q = htmlentities($_GET['term']);
			$modelRestaurant = new Model_Restaurant();
			$suggestions = array();
			$list = $modelRestaurant->filterTags ($q, $restaurants);
			foreach ($list as $tag) {
				$suggestions[] = array();
				$indice = count($suggestions) - 1;
				$suggestions[$indice]['id'] = $tag->id;
				$suggestions[$indice]['value'] = utf8_encode($tag->nom);
			}
			echo json_encode($suggestions);
		}
	}
	
	private function autocompleteRestaurant ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		if(isset($_GET['term'])) {
			$restaurants = $_GET['restaurant'];
			// Mot tapé par l'utilisateur
			$q = htmlentities($_GET['term']);
			$modelRestaurant = new Model_Restaurant();
			$suggestions = array();
			$list = $modelRestaurant->filterRestaurant ($q, $restaurants);
			foreach ($list as $restaurant) {
				$suggestions[] = array();
				$indice = count($suggestions) - 1;
				$suggestions[$indice]['id'] = $restaurant->id;
				$suggestions[$indice]['value'] = utf8_encode($restaurant->nom);
			}
			echo json_encode($suggestions);
		}
	}
	
	private function getLogo ($restaurant) {
		$logoDirectory = WEBSITE_PATH."res/img/restaurant/";
		if (file_exists($logoDirectory.$restaurant->id)) {
			if (file_exists($logoDirectory.$restaurant->id.'/logo.png')) {
				$restaurant->logo = $restaurant->id.'/logo.png';
			} else if (file_exists($logoDirectory.$restaurant->id.'/logo.jpg')) {
				$restaurant->logo = $restaurant->id.'/logo.jpg';
			} else if (file_exists($logoDirectory.$restaurant->id.'/logo.gif')) {
				$restaurant->logo = $restaurant->id.'/logo.gif';
			} else {
				$restaurant->logo = 'default/logo.jpg';
			}
		} else {
			$restaurant->logo = 'default/logo.jpg';
		}
	}
}