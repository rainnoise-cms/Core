<?php
namespace App;

class Core {
	public $params;
	private $defaults;
	private $config;
	private $data;

	public function __construct() {
		\set_exception_handler(array($this, 'exceptionHandler'));
		$this->data = [];
		$configPath = $_SERVER['DOCUMENT_ROOT'] . '/Configs/Core.json';

		// Read core config
		$this->defaults = json_decode(\file_get_contents(__DIR__ . '/config.json'), true);
		if (!$this->defaults) {
			throw new \RuntimeException("Cannot read core defaults");
		}
		$this->config = json_decode(@\file_get_contents($configPath), true);

		// Constructs request data
		$_buffer = explode('/', $_GET['request']);
		
		
		$this->params = [
			'request' => [
				'module' => @$_buffer[0],
				'action' => @$_buffer[1],
				'id' => @$_buffer[2]
			]
		];
	}
	
	/**
	 * Core entry point
	 */
	public function run() {
		$moduleName = $this->params['request']['module'] ?: $this->cfg('defaultModule');
		$modulePath = "\\Modules\\{$moduleName}\\Controller";
		
		
		if (!class_exists($modulePath)) {
			throw new \RuntimeException("Module '$moduleName' does not exist");
		}

		$module = new $modulePath($this);

		$action = $this->params['request']['action'];
		$output = $this->runAction($module, $action, $_GET + $_POST + $this->params['request']);
		echo "slkdjfklsd" .$output. ".,m.m.,m,.m.,";
	}

	private function runAction(BaseController $module, $action, array $params = []) {
		
		return $module->runAction($action, $this, $params);
		
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
		return  $result;
	}

	public final function exceptionHandler($exception) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');

		\ob_end_clean();
		
		echo $this->compileTemplate(__DIR__ . '/templates/Exception.tpl', $exception);
		die;
	}

	private function compileTemplate($filename, $data) {
		$this->data = $data;
		ob_start();
		eval('?>' . file_get_contents($filename) . '<?');
		return \ob_get_clean();
	}

	private function resPath(){
		return '/App/resources';
	}
}