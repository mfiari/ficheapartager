<?php
	
	$dom = new DOMDocument();
	$formulesDom = $dom->createElement("formules");
	$dom->appendChild($formulesDom);
	$nbResult = 0;
	foreach ($formules as $code => $value) {
		$formule = $dom->createElement("formule");
		$formulesDom->appendChild($formule);
		$formule->setAttribute("code", $code);
		$nodeNom = $dom->createElement("nom");
		$texteNom = $dom->createTextNode($value["nom"]);
		$nodeNom->appendChild($texteNom);
		$formule->appendChild($nodeNom);
		$nodeCategories = $dom->createElement("categories");
		$categories = $value["categorie"];
		foreach ($categories as $indice => $categorie) {
			$nodeCategorie = $dom->createElement("categorie");
			$nodeCategorie->setAttribute("id", $categorie["id"]);
			$nodeNomCategorie = $dom->createElement("nom");
			$texteNomCategorie = $dom->createTextNode($categorie["nom"]);
			$nodeNomCategorie->appendChild($texteNomCategorie);
			$nodeCategorie->appendChild($nodeNomCategorie);
			$nodeCategories->appendChild($nodeCategorie);
		}
		$formule->appendChild($nodeCategories);
		$nbResult++;
	}
	$formulesDom->setAttribute("nbResult", $nbResult);
	header("Content-type: text/xml; charset=utf-8");
	print $dom->saveXML();
	
?>