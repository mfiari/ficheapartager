<?php

ini_set('display_errors', '1');
ini_set('max_execution_time', 120);

include_once "../config.php";
include_once WEBSITE_PATH."core/Request.php";
include_once ROOT_PATH."function.php";

session_start();

$request = new Request();

$request->request_method = $_SERVER['REQUEST_METHOD'];

if (isset($_SESSION["uid"])) {
	include_once ROOT_PATH."models/Template.php";
	include_once ROOT_PATH."models/User.php";
	$user = new Model_User();
	$user->id = $_SESSION["uid"];
	if ($user->getById()) {
		$request->_auth = $user;
	} else {
		session_destroy();
	}
}

include_once WEBSITE_PATH."modules/default/Manager.php";
$manager = new Default_Manager();
$manager->dispatch($request);

if ($request->noRender === false) {
	if ($request->disableLayout) {
		require $request->vue;
	} else {
		require WEBSITE_PATH.'layouts/page.php';
	}
}

?>