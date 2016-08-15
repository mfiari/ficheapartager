<?php
	include("config.php");
	switch ($_SERVER['REQUEST_METHOD']) {
	  case "GET": 
		do_get();
		break;
	  case "POST":
		do_post();
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
		if (($id_user = getGetValue("id_user")) === false) {
			sendErrorMessage (400, "Missing parameters");
			die();
		}
		$sql = "SELECT pc.id, pc.nom FROM private_categorie pc 
			JOIN subscribe_categorie sc ON sc.id_categorie = pc.id
			WHERE sc.id_user = :id_user ORDER BY nom";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id_user", $id_user);
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
		/* insert */
		if (!hasAllPostValue(array("id_user", "identifiant", "password"))) {
			sendErrorMessage (400, "Missing arguments.");
		}
		$identifiant = getPostValue("identifiant");
		$password = getPostValue("password");
		$id_user = getPostValue("id_user");
		$db = getConnexion();
		/* ouvre une transaction */
		try {
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();
			
			$sql = "SELECT id, nom FROM private_categorie WHERE code = :code AND password = :password";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":code", $identifiant);
			$stmt->bindValue(":password", $password);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête : ");
				var_dump($stmt->errorInfo());
				die();
			}
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row == null) {
				sendErrorMessage (404, "Ce répertoire n'existe pas.");
				die();
			}
			
			$id_categorie = $row['id'];
			$nom_categorie = $row['nom'];
			
			$sql = "SELECT id_categorie FROM subscribe_categorie WHERE id_categorie = :id AND id_user = :user";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id", $id_categorie);
			$stmt->bindValue(":user", $id_user);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête : ");
				var_dump($stmt->errorInfo());
				die();
			}
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row != null) {
				sendErrorMessage (404, "Ce répertoire est déjà enregistrer.");
				die();
			}
			
			$sql = "INSERT INTO subscribe_categorie (id_categorie, id_user) VALUES (:id_categorie, :id_user)";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id_categorie", $id_categorie);
			$stmt->bindValue(":id_user", $id_user);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête.");
				die();
			}
			$nb = $stmt->rowCount();
			if ($nb == 0) {
				sendErrorMessage (500, "No data insert.");
			} else {
				$db->commit();
			}
			$dom = new DOMDocument();
			$categorie = $dom->createElement("categorie");
			$dom->appendChild($categorie);
			$categorie->setAttribute("id", $id_categorie);
			$categorie->setAttribute("nom", $nom_categorie);
			header("Content-type: text/xml; charset=utf-8");
			print $dom->saveXML();
		} catch (Exception $e) {
			$db->rollBack();
			sendErrorMessage (500, "Erreur dans la requête.");
			echo "Failed: " . $e->getMessage();
		}
	}
	
	function do_delete () {
		if (!hasAllPostValue(array("id", "id_user"))) {
			sendErrorMessage (400, "Missing arguments.");
		}
		$id_categorie = getPostValue("id");
		$id_user = getPostValue("id_user");
		$db = getConnexion();
		$sql = "DELETE FROM subscribe_categorie WHERE id_categorie = :id_categorie AND id_user = :id_user";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id_categorie", $id_categorie);
		$stmt->bindValue(":id_user", $id_user);
		$result = $stmt->execute();
		if ($result) {
			$nb = $stmt->rowCount();
			if ($nb == 0) {
				sendErrorMessage (400, "Echec suppression");
			}
		} else {
			sendErrorMessage (400, "Erreur dans la requête");
		}
	}
	
?>