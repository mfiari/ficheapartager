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
	$categoriesDom->setAttribute("nbResult", $nbResult);
	header("Content-type: text/xml; charset=utf-8");
	print $dom->saveXML();
	
?>