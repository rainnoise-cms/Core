<?php

namespace Modules\MySql;

use \App\DatabaseController;

class Controller extends DatabaseController {
	/**
	 * @var \PDO
	 */
	private $connection;

	public function __construct(\App\Core $core){
		parent::__construct($core);

		//$connection = new PDO();
	}

	public function test() {
		
	}
}