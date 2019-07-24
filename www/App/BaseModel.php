<?php
namespace App;

abstract class BaseModel {
	private $data;

	public function __construct($data){
		$this->data = $data;
	}

	public static function request(DatabaseController $db, $whereData){
		
	}

	public function save(DatabaseController $db) {
		
	}
}