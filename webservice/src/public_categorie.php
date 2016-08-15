<?php
	include("config.php");
	switch ($_SERVER['REQUEST_METHOD']) {
	  case "GET": 
		do_get();
		break;
	  case "POST":
		do_post();
		break;
	  case "PUT":
		do_put();
		break;
	  case "DELETE":
		do_delete();
		break;
	  default:
		sendErrorMessage (405);
		die();
	}
	
	function do_get () {
		$db = getConnexion();
		if (($parent = getGetValue("parent")) === false) {
			$parent = 0;
		}
		$dom = new DOMDocument();
		$items = $dom->createElement("items");
		$dom->appendChild($items);
		$sql = "SELECT id, nom FROM public_categorie WHERE parent = :parent ORDER BY nom";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":parent", $parent);
		if (!$stmt->execute()) {
			sendErrorMessage (500, "Erreur dans la requête : ");
			var_dump($stmt->errorInfo());
			die();
		}
		$categories = $dom->createElement("categories");
		$items->appendChild($categories);
		$result = $stmt->fetchAll();
		$nbResult = 0;
		foreach ($result as $key => $value) {
			$categorie = $dom->createElement("categorie");
			$categories->appendChild($categorie);
			$categorie->setAttribute("id", $value["id"]);
			$categorie->setAttribute("nom", $value["nom"]);
			$nbResult++;
		}
		$categories->setAttribute("nbResult", $nbResult);
		$sql = "SELECT id, titre, text FROM public_fiche WHERE categorie = :parent ORDER BY titre";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":parent", $parent);
		if (!$stmt->execute()) {
			sendErrorMessage (500, "Erreur dans la requête : ");
			var_dump($stmt->errorInfo());
			die();
		}
		$fiches = $dom->createElement("fiches");
		$items->appendChild($fiches);
		$result = $stmt->fetchAll();
		$nbResult = 0;
		foreach ($result as $key => $value) {
			$fiche = $dom->createElement("fiche");
			$fiches->appendChild($fiche);
			$fiche->setAttribute("id", $value["id"]);
			$titleNode = $dom->createElement("title");
			$titleText = $dom->createTextNode($value["titre"]);
			$titleNode->appendChild($titleText);
			$fiche->appendChild($titleNode);
			$textNode = $dom->createElement("text");
			$textText = $dom->createTextNode($value["text"]);
			$textNode->appendChild($textText);
			$fiche->appendChild($textNode);
			$nbResult++;
		}
		$fiches->setAttribute("nbResult", $nbResult);
		header("Content-type: text/xml; charset=utf-8");
		print $dom->saveXML();
	}
	
	function do_post () {
		$db = getConnexion();
		if (($id = getPostValue("id")) !== false) {
			/* update */
			if (hasAllPostValue(array("nom", "parent"))) {
				$nom = getPostValue("nom");
				$parent = getPostValue("parent");
				$sql = "UPDATE public_categorie SET nom = :nom, parent = :parent WHERE id = :id";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":nom", $nom);
				$stmt->bindValue(":parent", $parent);
				$stmt->bindValue(":id", $id);
				if ($stmt->execute()) {
					$nb = $stmt->rowCount();
					if ($nb == 0) {
						sendErrorMessage (500, "No data insert.");
					} else {
						$dom = new DOMDocument();
						$categorie = $dom->createElement("categorie");
						$dom->appendChild($categorie);
						$categorie->setAttribute("id", $id);
						$categorie->setAttribute("nom", $nom);
						$categorie->setAttribute("parent", $parent);
						header("Content-type: text/xml; charset=utf-8");
						print $dom->saveXML();
					}
				}
			}
		} else {
			/* insert */
			if (hasAllPostValue(array("nom", "parent"))) {
				$nom = getPostValue("nom");
				$parent = getPostValue("parent");
				$sql = "INSERT INTO public_categorie (nom, parent) VALUES (:nom, :parent)";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":nom", $nom);
				$stmt->bindValue(":parent", $parent);
				if ($stmt->execute()) {
					$nb = $stmt->rowCount();
					if ($nb == 0) {
						sendErrorMessage (500, "No data insert.");
					} else {
						$id_categorie = $db->lastInsertId("public_categorie_id_seq");
						$dom = new DOMDocument();
						$categorie = $dom->createElement("categorie");
						$dom->appendChild($categorie);
						$categorie->setAttribute("id", $id_categorie);
						$categorie->setAttribute("nom", $nom);
						$categorie->setAttribute("parent", $parent);
						header("Content-type: text/xml; charset=utf-8");
						print $dom->saveXML();
					}
				}
			}
		}
	}
	
	function do_put () {
		
	}
	
	function do_delete () {
		$db = getConnexion();
		$id = getGetValue("id");
		$sql = "DELETE FROM public_fiche WHERE categorie = :categorie";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":categorie", $id);
		if (!$stmt->execute()) {
			sendErrorMessage (500, "Erreur dans la requête : ");
			var_dump($stmt->errorInfo());
		}
		$sql = "DELETE FROM public_categorie WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id", $id);
		if (!$stmt->execute()) {
			sendErrorMessage (500, "Erreur dans la requête : ");
			var_dump($stmt->errorInfo());
		}
	}
	
?>