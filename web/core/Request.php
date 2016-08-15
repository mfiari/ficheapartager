<?php

class Request {
	
	private $params;
	
	public function __construct () {
		$this->params = array();
	}
	
	public function __get ($property) {
		if (!isset($this->params[$property])) {
			return false;
		}
		return $this->params[$property]; 
	}
	
	public function __set ($property, $value) {
		$this->params[$property] = $value;
	}
	
	public function hasProperty ($property) {
		return isset($this->params[$property]);
	}
}