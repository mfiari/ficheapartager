<?php

include_once WEBSITE_PATH."core/ControllerTemplate.php";

abstract class Controller_Default_Template extends Controller_Template {
	
	public function __construct () {
		
	}
	
	protected function render ($vue) {
		return WEBSITE_PATH."modules/default/vues/".$vue;
	}
}
