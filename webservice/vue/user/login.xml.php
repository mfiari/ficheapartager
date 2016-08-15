<?php
	
	$dom = new DOMDocument();
	$userDom = $dom->createElement("user");
	$dom->appendChild($userDom);
	$userDom->setAttribute("uid", $user->id);
	$nodeNom = $dom->createElement("nom");
	$texteNom = $dom->createTextNode($user->nom);
	$nodeNom->appendChild($texteNom);
	$userDom->appendChild($nodeNom);
	$nodeEmail = $dom->createElement("email");
	$texteEmail = $dom->createTextNode($user->email);
	$nodeEmail->appendChild($texteEmail);
	$userDom->appendChild($nodeEmail);
	$nodeCompte = $dom->createElement("compte");
	$texteCompte = $dom->createTextNode($user->compte);
	$nodeCompte->appendChild($texteCompte);
	$userDom->appendChild($nodeCompte);
	header("Content-type: text/xml; charset=utf-8");
	print $dom->saveXML();
	
?>