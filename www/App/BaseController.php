<?php
namespace App;

abstract class BaseController {
	private $Core;
	private $defaults;
	private $config;

	protected function __construct(Core $core){
		$configPath = $_SERVER['DOCUMENT_ROOT'] . '/Configs/' . $this->getModuleName() . '.json';

		$reflector = new \ReflectionClass(get_class($this));
		$this->defaults = json_decode(\file_get_contents(dirname($reflector->getFileName()) . '/config.json'), true);

		if (!$this->defaults) {
			throw new \RuntimeException("Cannot read module defaults");
		}

		$this->config = json_decode(\file_get_contents($configPath), true);
	}

	protected function cfg($cfgName) {
		if (isset($this->config[$cfgName])) {
			$result = $this->config[$cfgName];
		}
		elseif (isset($this->defaults['cfg'][$cfgName]['default'])) {
			$result = $this->defaults['cfg'][$cfgName]['default'];
		}
		else {
			throw new \UnexpectedValueException("Unknown parameter '$cfgName'");
		}
		 

		//print_r($result);
		return $result;
	}

	private function getModuleName() {
		$matches = [];
		
		if (!\preg_match("/^.*?\\\\(.*?)\\\\/", \get_class($this), $matches)) {
			throw new \LogicException('Cannot resolve module name');
		};
		return $matches[1];
	}

	public function runAction($action, \App\Core $core, array $params = []) {
		$action = $action ?: $this->defaults['defaultAction'];

		if (empty($action)) {
			throw new \RuntimeException("Empty action name");
		}
		\ob_start();
		$this->{"action_" . $action}($params, $core);
		return \ob_get_clean();
	}
}