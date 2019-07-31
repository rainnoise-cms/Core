<?php

namespace App;

use App\Helpers\Request;
use App\Services\ModuleConfig;
use LogicException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class Controller implements ModuleInterface
{
	private $Core;
	private $config;

	/**
	 * Controller constructor.
	 * @param Core $core
	 * @throws ReflectionException
	 */
	public function __construct(Core $core)
	{
		$this->Core = $core;
		$reflector = new ReflectionClass(get_class($this));
		$this->config = new ModuleConfig(dirname($reflector->getFileName()));
	}

	/**
	 * @param $cfgName
	 * @return mixed
	 */
	protected function cfg($cfgName)
	{
		return $this->config->cfg($cfgName);
	}

	/**
	 * @return string - Current module name
	 */
	public function moduleName()
	{
		if (!preg_match("/^\\\\?Modules\\\\(.+?)\\\\/", get_class($this), $matches)) {
			throw new LogicException('Cannot resolve module name');
		};

		return $matches[1];
	}

	/**
	 * @param Core $core
	 * @param array $params
	 * @return false|string
	 * @throws ReflectionException
	 */
	public function run(Core $core, array $params = [])
	{
		$request = Request::resolve($params['request']);

		if (!in_array($request->actionName, $this->actionsList()) &&
				$this->cfg('defaultAction')) {
			$request->actionName = $this->cfg('defaultAction');
		}
		else {
			// 404 ?
		}

		if (empty($request->actionName)) {
			throw new RuntimeException("Empty action name");
		}
		ob_start();
		$this->{"action_" . $request->actionName}($params, $core);
		return $this->actionEnd();
	}

	/**
	 * @return false|string
	 */
	protected function actionEnd()
	{
		return ob_get_clean();
	}

	/**
	 * @return array|string[]
	 */
	public function getEvents() {
		return $this->config->getEvents();
	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	protected function actionsList() {
		$reflection = new ReflectionClass($this);

		$methods = array_filter(
			$reflection->getMethods(),
			/**
			 * @noinspection PhpFullyQualifiedNameUsageInspection
			 * @param \ReflectionMethod $method
			 * @return bool
			 */
			function ($method) {
				return $method->isPublic() && preg_match('#^action_([A-Za-z].+)$#', $method->getName());
			}
		);

		return array_values(array_map(function($method) {
			return preg_replace('#^action_([A-Za-z].+)$#', '$1', $method->name);
		}, $methods));
	}
}