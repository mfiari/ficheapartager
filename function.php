<?php

function generateToken ($length = 32) {
	$characters = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_";
	$chaine = "";
	$nbChar = strlen($characters)-1;
	while (strlen($chaine) < $length) {
		$alea = rand(0, $nbChar);
		$char = $characters[$alea];
		$chaine .= $char;
	}
	return $chaine;
}

function generatePassword ($length = 8) {
	$characters = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_#*$+=";
	$chaine = "";
	$nbChar = strlen($characters);
	while (strlen($chaine) < $length) {
		$alea = rand(0, $nbChar);
		$char = $characters[$alea];
		$chaine .= $char;
	}
	return $chaine;
}

function writeLog ($type, $texte, $level = LOG_LEVEL_INFO, $message = null) {
	$directory = "log";
	$filename = "log_".date('Y-m-d');
	switch ($type) {
		case SQL_LOG :
		    /* écrit dans le fichier log/sql/log_aaaa-mm-dd */
			$directory = "log/sql/";
			break;
		case VIEW_LOG :
			/* écrit dans le fichier log/view/log_aaaa-mm-dd */
			$directory = "log/view/";
			break;
		case SERVER_LOG :
			/* écrit dans le fichier log/server/log_aaaa-mm-dd */
			$directory = "log/server/";
			break;
		case WS_LOG :
			/* écrit dans le fichier log/ws/log_aaaa-mm-dd */
			$directory = "log/ws/";
			break;
		case CRON_LOG :
			/* écrit dans le fichier log/cron/log_aaaa-mm-dd */
			$directory = "log/cron/";
			break;
		case MAIL_LOG :
			/* écrit dans le fichier log/cron/log_aaaa-mm-dd */
			$directory = "log/mail/";
			break;
	}
	
	$mailMessage = "";
	
	$logfile = fopen(ROOT_PATH.$directory.$filename, "a");
	$debug = debug_backtrace();
	fwrite($logfile, "[BEGIN_LOG]\n");
	fwrite($logfile, 'LEVEL : '.$level);
	fwrite($logfile, 'DATE : ' .date('Y-m-d h:i:s'));
	$mailMessage .= 'date : ' .date('Y-m-d h:i:s').'<br /><br />';
	fwrite($logfile, 'FILE : '.$debug[0]['file']."\n");
	$mailMessage .= 'file : '.$debug[0]['file'].'<br /><br />';
	fwrite($logfile, 'FUNCTION : '.$debug[0]['function']."\n");
	$mailMessage .= 'function : '.$debug[0]['function'].'<br /><br />';
	fwrite($logfile, 'LINE : '.$debug[0]['line']."\n");
	$mailMessage .= 'line : '.$debug[0]['line'].'<br /><br />';
	if ($message !== null) {
		fwrite($logfile, 'MESSAGE : '.$message."\n");
		$mailMessage .= 'message : '.$message.'<br /><br />';
	}
	fwrite($logfile, 'TEXT : ');
	$mailMessage .= 'text : <br />';
	if (is_array($texte)) {
		foreach ($texte as $key => $value) {
			fwrite($logfile, $key. ' => '.$value."\n");
			$mailMessage .= $key. ' => '.$value.'<br />';
		}
		$mailMessage .= '<br />';
	} else {
		fwrite($logfile, $texte."\n");
		$mailMessage .= $texte.'<br /><br />';
	}
	fwrite($logfile, "[END_LOG]\n");
	fclose($logfile);
	if ($level == LOG_LEVEL_ERROR) {
		send_mail ("informatique@homemenus.fr", "Erreur de type ".$type, $mailMessage);
	}
}

function send_mail ($to, $subject, $message, $from = false) {
	
	if (ENVIRONNEMENT != "PRODUCTION") {
		return true;
	}
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	
	if (!$from) {
		$headers .= "From: no-reply@homemenus.fr";
	} else {
		$headers .= "From: $from";
	}
	
	if (!mail($to,$subject,$message,$headers)) {
		writeLog (MAIL_LOG, $message, LOG_LEVEL_ERROR, $subject, $to);
		return false;
	} 
	return true;
}

function datepickerToDatetime ($date) {
	list($day, $month, $year) = explode('/', $date);
	return $year.'-'.$month.'-'.$day;
}

function construit_url_paypal() {
	$api_paypal = 'https://api-3t.sandbox.paypal.com/nvp?'; // Site de l'API PayPal. On ajoute déjà le ? afin de concaténer directement les paramètres.
	$version = 56.0; // Version de l'API

	$user = 'cservichezvous_api1.gmail.com'; // Utilisateur API
	$pass = 'D24DDXJJCGUJYKR9'; // Mot de passe API
	$signature = 'ArNp9UkZWjxr7O8Wgxl1F5CN.u13A4.lDIpfU3dMcFsPcOT7XEZ6ubVT'; // Signature de l'API

	$api_paypal = $api_paypal.'VERSION='.$version.'&USER='.$user.'&PWD='.$pass.'&SIGNATURE='.$signature; // Ajoute tous les paramètres

	return 	$api_paypal; // Renvoie la chaîne contenant tous nos paramètres.
}

function recup_param_paypal($resultat_paypal) {
	// On récupère la liste de paramètres, séparés par un 'et' commercial (&)
	$liste_parametres = explode("&",$resultat_paypal); // Crée un tableau de paramètres
	// Pour chacun de ces paramètres, on exécute le bloc suivant, en intégrant le paramètre dans la variable $param_paypal
	foreach($liste_parametres as $param_paypal)
	{
		// On récupère le nom du paramètre et sa valeur dans 2 variables différentes. Elles sont séparées par le signe égal (=)
		list($nom, $valeur) = explode("=", $param_paypal);
		// On crée un tableau contenant le nom du paramètre comme identifiant et la valeur comme valeur.
		$liste_param_paypal[$nom]=urldecode($valeur); // Décode toutes les séquences %##  et les remplace par leur valeur.
	}
	return $liste_param_paypal; // Retourne l'array
}

function addTextNode ($dom, $parent, $name, $value) {
	$node = $dom->createElement($name);
	$texte = $dom->createTextNode($value);
	$node->appendChild($texte);
	$parent->appendChild($node);
}

?>
