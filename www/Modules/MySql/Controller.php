<?php

namespace Modules\MySql;

use App\Core;
use App\DatabaseInterface;
use PDO;


class Controller implements DatabaseInterface
{
	/**
	 * @var PDO
	 */
	private $connection;

	public function __construct(Core $core){

		//$connection = new PDO();
	}

	public function insert($tableName, array $data)
	{
		// TODO: Implement Insert() method.
	}

	public function select($tableName, array $columns, array $whereConds, $order, array $limit)
	{
		// TODO: Implement Select() method.
	}
}