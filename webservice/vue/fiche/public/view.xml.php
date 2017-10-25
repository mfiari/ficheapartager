<?php
	
	$dom = new DOMDocument();
	$ficheDom = $dom->createElement("fiche");
	$dom->appendChild($ficheDom);
	$ficheDom->setAttribute("id", $fiche->id);
	$nodeTitre = $dom->createElement("titre");
	$texteNom = $dom->createTextNode($fiche->titre);
	$nodeTitre->appendChild($texteNom);
	$ficheDom->appendChild($nodeTitre);
	$nodeText = $dom->createElement("text");
	$texte = $dom->createTextNode($fiche->text);
	$nodeText->appendChild($texte);
	$ficheDom->appendChild($nodeText);
	$nodeImage = $dom->createElement("url_image");
	$texteImage = $dom->createTextNode($fiche->url_image);
	$nodeImage->appendChild($texteImage);
	$ficheDom->appendChild($nodeImage);
	header("Content-type: text/xml; charset=utf-8");
	print $dom->saveXML();
	
?>