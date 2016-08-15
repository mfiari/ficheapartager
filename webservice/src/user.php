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
		if (($uid = getUid()) !== false) {
			$db = getConnexion();
			$sql = "SELECT login, email, compte FROM users where uid = :uid";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":uid", $uid);
			$result = $stmt->execute();
			if ($result) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row != null) {
					header("Content-type: text/xml; charset=utf-8");
					$dom = new DOMDocument();
					$user = $dom->createElement("user");
					$user->setAttribute("id", $uid);
					$user->setAttribute("email", $row['email']);
					$user->setAttribute("login", $row['login']);
					$user->setAttribute("compte", $row['compte']);
					$dom->appendChild($user);
					print $dom->saveXML();
				} else {
					sendErrorMessage (400, "cet utilisateur n'existe pas.");
				}
			} else {
				sendErrorMessage (500, "Erreur dans la requête.");
			}
		} else {
			sendErrorMessage (400, "Login ou password incorrect.");
			echo "PHP_AUTH_USER : ".$_SERVER["PHP_AUTH_USER"];
			echo "PHP_AUTH_PW : ".$_SERVER["PHP_AUTH_PW"];
		}
	}
	
	function do_post () {
		/* insert */
		if (!hasAllPostValue(array("login", "password", "email", "compte"))) {
			sendErrorMessage (400, "Missing arguments.");
			die();
		}
		$login = getPostValue("login");
		$password = getPostValue("password");
		$email = getPostValue("email");
		$compte = getPostValue("compte");
		$db = getConnexion();
		/* ouvre une transaction */
		try {
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = "INSERT INTO users (login, password, email, compte) VALUES (:login, :password, :email, :compte) RETURNING uid";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":login", $login);
			$stmt->bindValue(":password", $password);
			$stmt->bindValue(":email", $email);
			$stmt->bindValue(":compte", $compte);
			$stmt->execute();
			$nb = $stmt->rowCount();
			if ($nb == 0) {
				sendErrorMessage (500, "No data insert.");
			} else {
				$uid = $db->lastInsertId("users_uid_seq");
				$db->commit();
				header("Content-type: text/xml; charset=utf-8");
				$dom = new DOMDocument();
				$user = $dom->createElement("user");
				$user->setAttribute("id", $uid);
				$user->setAttribute("email", $email);
				$user->setAttribute("login", $login);
				$user->setAttribute("compte", $compte);
				$dom->appendChild($user);
				print $dom->saveXML();
			}
		} catch (Exception $e) {
			$db->rollBack();
			sendErrorMessage (500, "Erreur dans la requête.");
			echo "Failed: " . $e->getMessage();
		}
	}
	
	function do_put () {
	}
	
	function do_delete () {
		$id_user = getGetValue("id_user");
		$db = getConnexion();
		$sql = "DELETE FROM users WHERE uid = :uid";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":uid", $id_user);
		$result = $stmt->execute();
		if ($result) {
			$nb = $stmt->rowCount();
			if ($nb == 0) {
				sendErrorMessage (400, "Erreur dans la requête");
			}
		} else {
			sendErrorMessage (400, "Erreur dans la requête");
		}
	}
	
?>