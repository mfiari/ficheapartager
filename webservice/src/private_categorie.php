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
		$dom = new DOMDocument();
		$items = $dom->createElement("items");
		$dom->appendChild($items);
		if (($id_user = getGetValue("id_user")) !== false) {
			$sql = "SELECT id, nom FROM private_categorie WHERE id_user = :id_user ORDER BY nom";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id_user", $id_user);
		} else {
			$sql = "SELECT id, nom FROM private_categorie ORDER BY nom";
			$stmt = $db->prepare($sql);
		}
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
			if (hasAllPostValue(array("nom", "password"))) {
				$id_user = getPostValue("id_user");
				
				$sql = "SELECT login FROM users where uid = :uid";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":uid", $id_user);
				$result = $stmt->execute();
				if (!$result) {
					sendErrorMessage (500, "Erreur dans la requête.");
					die();
				}
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row == null) {
					sendErrorMessage (400, "cet utilisateur n'existe pas.");
					die();
				}
				
				$login = $row['login'];
				$code = substr($login, 0, 3);
				$nom = getPostValue("nom");
				$password = getPostValue("password");
				$sql = "INSERT INTO private_categorie (nom, password, id_user) VALUES (:nom, :password, :id_user)";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":nom", $nom);
				$stmt->bindValue(":password", $password);
				$stmt->bindValue(":id_user", $id_user);
				if (!$stmt->execute()) {
					sendErrorMessage (500, "Erreur dans la requête.");
					die();
				}
				$nb = $stmt->rowCount();
				if ($nb == 0) {
					sendErrorMessage (500, "No data insert.");
					die();
				}
				$id_categorie = $db->lastInsertId("private_categorie_id_seq");
				$code .= $id_categorie;
				$sql = "UPDATE private_categorie SET code = :code WHERE id = :id";
				$stmt = $db->prepare($sql);
				$stmt->bindValue(":code", $code);
				$stmt->bindValue(":id", $id_categorie);
				if (!$stmt->execute()) {
					sendErrorMessage (500, "Erreur dans la requête.");
					die();
				}
				$dom = new DOMDocument();
				$categorie = $dom->createElement("categorie");
				$dom->appendChild($categorie);
				$categorie->setAttribute("id", $id_categorie);
				$categorie->setAttribute("nom", $nom);
				$categorie->setAttribute("code", $code);
				header("Content-type: text/xml; charset=utf-8");
				print $dom->saveXML();
			}
		}
	}
	
	function do_put () {
		
	}
	
	function do_delete () {
		$db = getConnexion();
		$id = getGetValue("id");
		$sql = "DELETE FROM public_categorie WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id", $id);
		if ($stmt->execute()) {
			
		} else {
			sendErrorMessage (500, "Erreur dans la requête : ".$stmt->errorInfo());
		}
	}
	
?>