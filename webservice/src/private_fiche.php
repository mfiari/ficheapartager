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
		$sql = "SELECT id, titre, text FROM private_fiche WHERE categorie = :categorie ORDER BY titre";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":categorie", $parent);
		if ($stmt->execute()) {
			$dom = new DOMDocument();
			$items = $dom->createElement("items");
			$dom->appendChild($items);
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
		} else {
			sendErrorMessage (500, "Erreur dans la requête : ");
			var_dump($stmt->errorInfo());
		}
	}
	
	function do_post () {
		$db = getConnexion();
		if (($id = getPostValue("id")) !== false) {
			/* update */
			if (hasAllPostValue(array("title", "text", "id_user"))) {
				$title = getPostValue("title");
				$text = getPostValue("text");
				$id_user = getPostValue("id_user");
				$sql = "UPDATE private_fiche SET titre = :titre, text = :text WHERE id = :id";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":titre", $title);
				$stmt->bindValue(":text", $text);
				$stmt->bindValue(":id", $id);
				if ($stmt->execute()) {
					$nb = $stmt->rowCount();
					if ($nb == 0) {
						sendErrorMessage (500, "No data insert.");
					} else {
						$dom = new DOMDocument();
						$fiche = $dom->createElement("fiche");
						$dom->appendChild($fiche);
						$fiche->setAttribute("id", $id);
						$fiche->setAttribute("id_user", $id_user);
						$titleNode = $dom->createElement("title");
						$titleText = $dom->createTextNode($title);
						$titleNode->appendChild($titleText);
						$fiche->appendChild($titleNode);
						$textNode = $dom->createElement("text");
						$textText = $dom->createTextNode($text);
						$textNode->appendChild($textText);
						$fiche->appendChild($textNode);
						header("Content-type: text/xml; charset=utf-8");
						print $dom->saveXML();
					}
				}
			}
		} else {
			/* insert */
			if (hasAllPostValue(array("title", "text", "id_user", "categorie"))) {
				$title = getPostValue("title");
				$text = getPostValue("text");
				$id_user = getPostValue("id_user");
				$categorie = getPostValue("categorie");
				$sql = "INSERT INTO private_fiche (titre, categorie, text) VALUES (:titre, :categorie, :text)";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":titre", $title);
				$stmt->bindValue(":text", $text);
				$stmt->bindValue(":categorie", $categorie);
				if ($stmt->execute()) {
					$nb = $stmt->rowCount();
					if ($nb == 0) {
						sendErrorMessage (500, "No data insert.");
					} else {
						$id_fiche = $db->lastInsertId("private_fiche_id_seq");
						$dom = new DOMDocument();
						$fiche = $dom->createElement("fiche");
						$dom->appendChild($fiche);
						$fiche->setAttribute("id", $id_fiche);
						$fiche->setAttribute("id_user", $id_user);
						$fiche->setAttribute("categorie", $categorie);
						$titleNode = $dom->createElement("title");
						$titleText = $dom->createTextNode($title);
						$titleNode->appendChild($titleText);
						$fiche->appendChild($titleNode);
						$textNode = $dom->createElement("text");
						$textText = $dom->createTextNode($text);
						$textNode->appendChild($textText);
						$fiche->appendChild($textNode);
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
		$sql = "DELETE FROM private_fiche WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id", $id);
		if ($stmt->execute()) {
			
		} else {
			sendErrorMessage (500, "Erreur dans la requête : ".$stmt->errorInfo());
		}
	}
	
?>