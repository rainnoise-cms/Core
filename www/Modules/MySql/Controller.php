<?php

namespace Modules\MySql;

use App\Core;
use App\DatabaseInterface;
use App\ModuleInterface;
use PDO;

class Controller implements DatabaseInterface, ModuleInterface
{
	/**
	 * @var PDO
	 */
	private $connection;

	public function __construct(Core $core){

		//$connection = new PDO();
	}

	public function test() {
		
	}
}