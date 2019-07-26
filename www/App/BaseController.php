<?php

namespace App;

use App\Services\ModuleConfig;
use LogicException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class BaseController implements ModuleInterface
{
	private $Core;
	private $config;

	/**
	 * BaseController constructor.
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
	public function getModuleName()
	{
		if (!preg_match("/^\\\\Modules\\\\(.+?)/", get_class($this), $matches)) {
			throw new LogicException('Cannot resolve module name');
		};
		return $matches[1];
	}

	/**
	 * @param $action
	 * @param Core $core
	 * @param array $params
	 * @return false|string
	 */
	public function runAction($action, Core $core, array $params = [])
	{
		$action = $action ?: $this->cfg('defaultAction');

		if (empty($action)) {
			throw new RuntimeException("Empty action name");
		}
		ob_start();
		$this->{"action_" . $action}($params, $core);
		return ob_get_clean();
	}

	public function compileTemplate($filename)
	{
		// TODO: Implement compileTemplate() method.
	}
}