<?php

abstract class Controller_Template {
	
	public function __construct () {
		
	}
	
	protected function error ($code, $message = "") {
		header("HTTP/1.0 ".$code." ".$message);
		die();
	}
	
	protected function redirect ($action = '', $controller = '', $module = '', $params = null) {
		$redirectUrl = '';
		$separator = '';
		if (trim($module) != '') {
			$redirectUrl .= 'module='.$module;
			$separator = '&';
		}
		if (trim($controller) != '') {
			$redirectUrl .= $separator.'controler='.$controller;
			$separator = '&';
		}
		if (trim($action) != '') {
			$redirectUrl .= $separator.'action='.$action;
			$separator = '&';
		}
		if ($params != null) {
			foreach ($params as $key => $value) {
				$redirectUrl .= $separator.$key.'='.$value;
			}
		}
		if ($redirectUrl != '') {
			$redirectUrl = '?'.$redirectUrl;
		} else {
			$redirectUrl = 'index.php';
		}
		header('Location: '.$redirectUrl);
	}
}
