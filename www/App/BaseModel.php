<?php

namespace App;

abstract class BaseModel
{
	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public static function request(DatabaseInterface $db, $whereData)
	{

	}

	public function save(DatabaseInterface $db)
	{

	}
}