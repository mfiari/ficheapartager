<?php

abstract class Model_Template {

	protected $db;
	protected $sqlHasFailed;

	public function __construct($db = null){
		if ($db === null) {
			$this->db = new PDO("mysql:host=".MYSQL_HOST.";dbname=".MYSQL_DBNAME,MYSQL_LOGIN,MYSQL_PASSWORD);
			// Forcer la communication en utf-8
			$this->db->exec("SET character_set_client = 'utf8'");
		} else {
			$this->db = $db;
		}
		$this->sqlHasFailed = false;
	}
	
	public function beginTransaction () {
		
	}
	
	public function endTransaction () {
		if ($this->sqlHasFailed === true) {
			/* rollback */
		} else {
			/* commit */
		}
	}
	
	public function getDbConnector () {
		return $this->db;
	}
	
	public function request ($sql, $params) {
		
	}
}
