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
	private $core;
	private $config;

	/**
	 * Controller constructor.
	 * @param Core $core
	 * @throws ReflectionException
	 */
	public function __construct(Core $core)
	{
		$this->core = $core;
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

		if (!in_array($request->actionName, $this->listActions()) &&
				$this->cfg('defaultAction')) {
			$request->actionName = $this->cfg('defaultAction');
		}
		else {
			// 404 ?
		}

		if (empty($request->actionName)) {
			throw new RuntimeException("Empty action name");
		}
		return $this->runAction($request->actionName, $params);
	}

	public function runAction($actionName, $params)
	{
		ob_start();
		$this->{$this->actionPrefix() . $actionName}($params, $this->core);
		return ob_get_clean();
	}

	public function runEvent($eventName, $params, &$hook)
	{
		if (!in_array($eventName, $this->listEvents())) return null;

		ob_start();
		$this->{$this->eventPrefix() . $eventName}($params, $this->core, $hook);
		return ob_get_clean();
	}

	public function actionPrefix()
	{
		return 'action_';
	}

	public function eventPrefix()
	{
		return 'event_';
	}

	/**
	 * @return array|string[]
	 */
	public function listEvents() {
		return $this->config->getEvents();
	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	public function listActions() {
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