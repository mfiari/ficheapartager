<?php

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
include_once ROOT_PATH."models/PreCommande.php";

class Controller_Pre_Commande extends Controller_Default_Template {
	
	public function manage ($request) {
		if (isset($_GET["action"])) {
			$action = $_GET["action"];
			switch ($action) {
				case "search" :
					$this->search($request);
					break;
				case "commande_search" :
					$this->commande_search($request);
					break;
				case "restaurant" :
					$this->restaurant($request);
					break;
				case "categories" :
					$this->categories($request);
					break;
				case "carte" :
					$this->carte($request);
					break;
				case "addCarte" :
					$this->addCarte($request);
					break;
				case "menu" :
					$this->menu($request);
					break;
				case "loadCommandeMonth" :
					$this->loadCommandeMonth($request);
					break;
				case "loadCommandeDay" :
					$this->loadCommandeDay($request);
					break;
				case "detailCommande" :
					$this->detailCommande($request);
					break;
				case "validationCommande" :
					$this->validationCommande($request);
					break;
				case "payment" :
					$this->payment($request);
					break;
				default :
					$this->redirect('404');
					break;
			}
		}
	}
	
	public function search ($request) {
		unset($_SESSION['id_commande']);
		$request->date = $_POST['date'];
		$request->vue = $this->render("precommande/search.php");
	}
	
	public function commande_search ($request) {
		$city = "";
		if ($request->request_method == "GET") {
			if (!isset($_SESSION['search_serialized'])) {
				$this->redirect();
			}
			$filter = unserialize($_SESSION['search_serialized']);
		} else if ($request->request_method == "POST") {
			$filter = array();
			$filter["search_adresse"] = $_POST['adresse'];
			$request->search_date = $_POST['date'];
			list($day, $month, $year) = explode('/', $request->search_date);
			$filter["search_date"] = $year.'-'.$month.'-'.$day;
			$filter["search_hour"] = $_POST['hour'];
			$filter["search_minute"] = $_POST['minute'];
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
		$request->search_hour = $filter["search_hour"];
		$request->search_minute = $filter["search_minute"];
		
		$restaurants = $modelRestaurant->filter($filter);
		
		$request->search_adresse = $filter["search_adresse"];
		$geocoder = "https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false";
		$localisation = urlencode($filter["search_adresse"]);
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
			$_SESSION['search_date'] = $filter["search_date"];
			$_SESSION['search_hour'] = $filter["search_hour"];
			$_SESSION['search_minute'] = $filter["search_minute"];
			$_SESSION['search_adresse'] = $request->search_adresse;
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
				}
			}
			$restaurants = $availableRestaurant;
				
			foreach ($restaurants as $restaurant) {
				$restaurant->logo = getLogoRestaurant($restaurant->id);
			}
		} else {
			$request->adressError = true;
		}
		$request->ouvert = true;
		$request->distance = $distanceKm;
		$request->ville = $city;
		$request->villes = array_unique(array_object_column($restaurants, 'ville'));
		$request->restaurants = $restaurants;
		$request->vue = $this->render("precommande/restaurants.php");
	}
	
	public function restaurant ($request) {
		if (isset($_GET['id'])) {
			if (isset($_GET['id_commande'])) {
				$_SESSION['id_commande'] = $_GET['id_commande'];
			}
			$request->title = "Restaurant";
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$request->restaurant = $modelRestaurant->getOne();
			$modelCategorie = new Model_Categorie();
			$request->restaurant->categories = $modelCategorie->getParentContenu($request->restaurant->id);
			$request->search_adresse = $_SESSION['search_adresse'];
			$request->vue = $this->render("precommande/restaurant.php");
		}	
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
		$request->vue = $this->render("precommande/categories.php");
	}
	
	private function carte ($request) {
		if (isset($_GET["id_carte"])) {
			$request->disableLayout = true;
			$modelCarte = new Model_Carte();
			$modelCarte->id = $_GET['id_carte'];
			$request->id_restaurant = $_GET['id'];
			$request->carte = $modelCarte->load();
			$request->carte->getLogo($request->id_restaurant);
			
			$request->vue = $this->render("precommande/carteDetail.php");
		}
	}
	
	private function initCommande ($request, $id_restaurant) {
		$commande = new Model_Pre_Commande();
		$commande->uid = $request->_auth->id;
		$commande->init();
		if ($commande->id_restaurant == -1) {
			$restaurant = new Model_Restaurant();
			$restaurant->id = $id_restaurant;
			$fields = array ("latitude", "longitude");
			$restaurant->get($fields);
			$adresseResto = $restaurant->latitude.','.$restaurant->longitude;
			
			$user_latitude = $_SESSION['search_latitude'];
			$user_longitude = $_SESSION['search_longitude'];
			$adresseUser = $user_latitude.','.$user_longitude;
			
			$result = getDistance($adresseUser, $adresseResto);
			$distance = 0;
			if ($result['status'] == "OK") {
				$distance = $result['distance'] / 1000;
			}
			$commande->id_restaurant = $id_restaurant;
			$commande->rue = $_SESSION['search_rue'];
			$commande->ville = $_SESSION['search_ville'];
			$commande->code_postal = $_SESSION['search_cp'];
			$commande->latitude = $user_latitude;
			$commande->longitude = $user_longitude;
			$commande->distance = $distance;
			$commande->date_commande = $_SESSION['search_date'];
			$commande->heure_souhaite = $_SESSION['search_hour'];
			$commande->minute_souhaite = $_SESSION['search_minute'];
			$commande->update();
		} else if ($commande->id_restaurant != $id_restaurant) {
			$this->error(400, "Bad request");
		}
		return $commande;
	}
	
	public function addCarte ($request) {
		if ($request->request_method != "POST") {
			$this->error(405, "Method not allowed");
			return;
		}
		if (!$request->_auth) {
			$this->error(403, "Not authorized");
			return;
		}
		if (!isset($_POST['id_carte'])) {
			$this->error(409, "Conflict");
		}
		$request->disableLayout = true;
		$request->noRender = true;
		$id_restaurant = $_POST['id_restaurant'];
		if (isset($_SESSION['id_commande'])) {
			$commande = new Model_Pre_Commande();
			$commande->uid = $request->_auth->id;
			$commande->id = $_SESSION['id_commande'];
			$commande->id_restaurant = $id_restaurant;
		} else {
			$commande = $this->initCommande ($request, $id_restaurant);
			$_SESSION['id_commande'] = $commande->id;
		}
		$quantite = $_POST['quantite'];
		$id_carte = $_POST['id_carte'];
		$format = $_POST['format'];
		$modelCarte = new Model_Carte();
		$modelCarte->id = $id_carte;
		$modelCarte->load();
		$id_panier_carte = $commande->addCarte($id_carte, $format, $quantite);
		foreach ($modelCarte->options as $option) {
			foreach ($option->values as $value) {
				if (isset($_POST['check_option_'.$option->id.'_'.$value->id])) {
					$commande->addCarteOption($id_panier_carte, $option->id, $value->id);
				}
			}
		}
		foreach ($modelCarte->accompagnements as $accompagnement) {
			foreach ($accompagnement->cartes as $carte) {
				if (isset($_POST['check_accompagnement_'.$carte->id])) {
					$commande->addCarteAccompagnement($id_panier_carte, $carte->id);
				}
			}
		}
		$carte = $modelCarte->getSupplements();
		foreach ($carte->supplements as $supplement) {
			var_dump($supplement);
			if (isset($_POST['check_supplement_'.$supplement->id])) {
				$commande->addCarteSupplement($id_panier_carte, $supplement->id);
			}
		}
	}
	
	private function menu ($request) {
		if (isset($_GET["id_menu"])) {
			$request->disableLayout = true;
			$request->id_restaurant = $_GET['id'];
			$modelMenu = new Model_Menu();
			$modelMenu->id = $_GET['id_menu'];
			$request->menu = $modelMenu->load();
			
			$request->vue = $this->render("precommande/menu.php");
		} else {
			$modelRestaurant = new Model_Restaurant();
			$modelRestaurant->id = $_GET['id'];
			$restaurant = $modelRestaurant->loadMinInformation();
			$restaurant->loadMenus();
			$request->restaurant = $restaurant;
			$request->javascripts = array("res/js/menu.js");
			$request->vue = $this->render("precommande/menus.php");
		}
	}
	
	private function loadCommandeMonth ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		$month = $_GET['month'];
		$commande = new Model_Pre_Commande();
		$commande->uid = $request->_auth->id;
		$list = $commande->getCommandeInMonth($month);
		$retour = array();
		$currentDate = "";
		$previousDate = "";
		foreach ($list as $commande) {
			$currentDate = $commande->date_commande;
			if ($currentDate != $previousDate) {
				$retour[$currentDate] = array();
				$previousDate = $currentDate;
			}
			$retour[$currentDate][] = array("id" => $commande->id);
		}
		echo json_encode($retour);
	}
	
	private function loadCommandeDay ($request) {
		$request->disableLayout = true;
		$request->noRender = true;
		$month = $_GET['month'];
		$day = $_GET['day'];
		$commande = new Model_Pre_Commande();
		$commande->uid = $request->_auth->id;
		$list = $commande->getCommandeDay($month, $day);
		$retour = array();
		foreach ($list as $commande) {
			$retour[] = array(
				"id" => $commande->id,
				"date" => $commande->daye_commande,
				"restaurant" => array (
					"id" => $commande->restaurant->id,
					"nom" => $commande->restaurant->nom
				)
			);
		}
		echo json_encode($retour);
	}
	
	private function detailCommande ($request) {
		$commande = new Model_Pre_Commande();
		$commande->id = $_GET['id_commande'];
		$request->commande = $commande->load();
		$request->vue = $this->render("precommande/commande.php");
	}
	
	private function validationCommande ($request) {
		$commande = new Model_Pre_Commande();
		$commande->id = $_GET['commande'];
		$request->commande = $commande->get();
		$request->vue = $this->render("precommande/validation.php");
	}
	
	private function payment ($request) {
		$commande = new Model_Pre_Commande();
		$commande->id = $_POST['id_commande'];
		$commande->get();
		$paymentMode = $_POST['payment'];
		if ($paymentMode == 'solde') {
			
		} else if ($paymentMode == 'paypal') {
			
		} else {
			
		}
		$request->id_commande = $commande->id;
		$this->confirmPayment ($request);
	}
	
	private function confirmPayment ($request) {
		$preCommande = new Model_Pre_Commande();
		if (isset($_GET['id_commande'])) {
			$preCommande->id = $_GET['id_commande'];
		} else {
			$preCommande->id = $request->id_commande;
		}
		$preCommande->payment = "PAYPAL";
		$preCommande->validate();
		$request->vue = $this->render("precommande/payment_success.php");
	}
}