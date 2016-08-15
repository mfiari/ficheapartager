<?php

abstract class Controller_Template {
	
	protected $ext;
	
	protected function init () {
		$this->ext = $this->getExtension ();
	}
	
	protected function error ($code, $message = "") {
		header("HTTP/1.0 ".$code." ".$message);
		die();
	}
	
	protected function getExtension () {
		if (isset($_GET["ext"])) {
			$ext = $_GET["ext"];
			return $ext;
		}
		return "xml";
	}
}
