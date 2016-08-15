<?php

$host = "localhost";
$port = 5432;
$dbname="ficherevision";
$user = "postgres";
$password = "sDKD8j43";

/** Fournit une connection à la base de données, en UTF-8 */
function getConnexion() {
  /*$db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");*/
  $db = new PDO("pgsql:host=localhost;port=5432;dbname=ficherevision;user=postgres;password=sDKD8j43");
  // Forcer la communication en utf-8
  $db->exec("SET character_set_client = 'utf8'");
  return $db;
}

/** L'utilisateur HTTP est-il administrateur des artistes ?
 * Ici, code très simple : seul l'utilisateur admin/admin est admin */
function getUid() {
	if (isset($_SERVER["PHP_AUTH_USER"])) {
		$bdd = getConnexion();
		$sql = "SELECT uid FROM users WHERE login = :login AND password = :pwd";
		$stmt = $bdd->prepare($sql);
		$stmt->bindValue(":login", $_SERVER["PHP_AUTH_USER"]);
		$stmt->bindValue(":pwd", $_SERVER["PHP_AUTH_PW"]);
		$result = $stmt->execute();
		if ($result) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row != null) {
				return $row['uid'];
			}
		}
	}
	return false;
}

function getSchemaName ($uid) {
	return "user_".$uid;
}

function createSuccess ($dom, $code, $msg) {
	$results = $dom->createElement("results");
	$dom->appendChild($results);
	$succes = $dom->createElement("succes");
	$succes->setAttribute("code", $code);
	$message = $dom->createElement("message");
	$message->setAttribute("info", $msg);
	$results->appendChild($succes);
	$results->appendChild($message);
}

function hasAllPostValue ($values) {
	foreach ($values as $value) {
		if (!isset($_POST[$value]) || trim($_POST[$value]) == "") {
			return false;
		}
	}
	return true;
}

function getPostValue ($value) {
	if (isset($_POST[$value]) && $_POST[$value] != "") {
		return $_POST[$value];
	}
	return false;
}

function getGetValue ($value) {
	if (isset($_GET[$value]) && $_GET[$value] != "") {
		return $_GET[$value];
	}
	return false;
}

function getPutValue ($value) {
	parse_str(file_get_contents("php://input"),$put_vars);
	var_dump($put_vars);
	if (isset($put_vars[$value]) && $put_vars[$value] != "") {
		return $put_vars[$value];
	}
	return false;
}

function sendErrorMessage ($code, $msg = "") {
	//send_status($code);
	print $code." : ".$msg;
	header("HTTP/1.0 ".$code." ".$msg);
}

?>