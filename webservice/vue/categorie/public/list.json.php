<?php
	
	$retour = array(
		"categories" => array(),
		"parent" => array()
	);
	
	if ($path !== false) {
		$retour["parent"] = addParent ($path);
	}
	
	foreach ($list as $categorie) {
		$categorieArray = array();
		$categorieArray['id'] = $categorie->id;
		$categorieArray['nom'] = utf8_encode($categorie->nom);
		$categorieArray['parent'] = $categorie->parent_categorie;
		$categorieArray['ordre'] = $categorie->ordre;
		
		$retour["categories"][] = $categorieArray;
	}
	
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($retour);
	
	function addParent ($parent) {
		$array = array();
		$array['id'] = $parent->id;
		$array['nom'] = $parent->nom;
		if (count($parent->childrens) > 0) {
			$array['childrens'] = addParent($parent->childrens[0]);
		}
		return $array;
	}
	
?>