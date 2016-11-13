<?php
	
	$dom = new DOMDocument();
	$categoriesDom = $dom->createElement("categories");
	$dom->appendChild($categoriesDom);
	$nbResult = 0;
	foreach ($list as $categorie) {
		$categorieDom = $dom->createElement("categorie");
		$categorieDom->setAttribute("id", $categorie->id);
		$nodeNom = $dom->createElement("nom");
		$texteNom = $dom->createTextNode(utf8_encode($categorie->nom));
		$nodeNom->appendChild($texteNom);
		$categorieDom->appendChild($nodeNom);
		$categoriesDom->appendChild($categorieDom);
		$nbResult++;
	}
	
	$fichesDom = $dom->createElement("fiches");
	foreach ($fiches as $fiche) {
		$ficheDom = $dom->createElement("fiche");
		$ficheDom->setAttribute("id", $fiche->id);
		$nodeTitre = $dom->createElement("titre");
		$texteTitre = $dom->createTextNode(utf8_encode($fiche->titre));
		$nodeTitre->appendChild($texteTitre);
		$ficheDom->appendChild($nodeTitre);
		$fichesDom->appendChild($ficheDom);
		$nbResult++;
	}
	$categoriesDom->appendChild($fichesDom);
	$categoriesDom->setAttribute("nbResult", $nbResult);
	header("Content-type: text/xml; charset=utf-8");
	print $dom->saveXML();
	
?>