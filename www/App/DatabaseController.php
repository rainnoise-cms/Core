<?php
namespace App;

abstract class DatabaseController {
	/**
	 * @var \PDO
	 */
	protected $connection;

	public function __construct(Core $core){

	}

}