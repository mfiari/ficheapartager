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
		if (isset($_GET["id_fiche"])) {
			$id_fiche = getGetValue("id_fiche");
			$type = getGetValue("type");
			$id_user = getGetValue("id_user");
			$sql = "SELECT id, titre, text FROM fiche_favorie JOIN public_fiche ON id = id_fiche WHERE type = :type AND fiche_favorie.id_user = :id_user AND id_fiche = :id";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":type", $type);
			$stmt->bindValue(":id_user", $id_user);
			$stmt->bindValue(":id", $id_fiche);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête : ");
				var_dump($stmt->errorInfo());
				die();
			}
		} else {
			$id_user = getGetValue("id_user");
			$dom = new DOMDocument();
			$items = $dom->createElement("items");
			$dom->appendChild($items);
			$fiches = $dom->createElement("fiches");
			$items->appendChild($fiches);
			$nbResult = 0;
			$sql = "SELECT id, titre, text FROM fiche_favorie JOIN public_fiche ON id = id_fiche WHERE type = 'public' AND fiche_favorie.id_user = :id_user ORDER BY titre";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id_user", $id_user);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête : ");
				var_dump($stmt->errorInfo());
				die();
			}
			$result = $stmt->fetchAll();
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
			$sql = "SELECT id, titre, text FROM fiche_favorie JOIN private_fiche ON id = id_fiche WHERE type = 'private' AND fiche_favorie.id_user = :id_user ORDER BY titre";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id_user", $id_user);
			if (!$stmt->execute()) {
				sendErrorMessage (500, "Erreur dans la requête : ");
				var_dump($stmt->errorInfo());
				die();
			}
			$result = $stmt->fetchAll();
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
	}
	
	function do_post () {
		/* insert */
		if (!hasAllPostValue(array("id_fiche", "type", "id_user"))) {
			sendErrorMessage (400, "Missing arguments.");
		}
		$id_fiche = getPostValue("id_fiche");
		$type = getPostValue("type");
		$id_user = getPostValue("id_user");
		$db = getConnexion();
		/* ouvre une transaction */
		try {
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = "INSERT INTO fiche_favorie (id_fiche, type, id_user) VALUES (:id_fiche, :type, :id_user)";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id_fiche", $id_fiche);
			$stmt->bindValue(":type", $type);
			$stmt->bindValue(":id_user", $id_user);
			$stmt->execute();
			$nb = $stmt->rowCount();
			if ($nb == 0) {
				sendErrorMessage (500, "No data insert.");
			} else {
				$db->commit();
			}
		} catch (Exception $e) {
			$db->rollBack();
			sendErrorMessage (500, "Erreur dans la requête.");
			echo "Failed: " . $e->getMessage();
		}
	}
	
	function do_delete () {
		if (!hasAllPostValue(array("id_fiche", "type", "id_user"))) {
			sendErrorMessage (400, "Missing arguments.");
		}
		$id_fiche = getPostValue("id_fiche");
		$type = getPostValue("type");
		$id_user = getPostValue("id_user");
		$db = getConnexion();
		$sql = "DELETE FROM fiche_favorie WHERE id_fiche = :id_fiche AND type = :type AND id_user = :id_user";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":id_fiche", $id_fiche);
		$stmt->bindValue(":type", $type);
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