<?php
	
	$retour = array();
	$retour["id"] = $user->id;
	$retour["nom"] = $user->nom;
	$retour["email"] = $user->email;
	$retour["compte"] = $user->compte;
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($retour);
	
?>