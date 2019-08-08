<?php

namespace App;

use App\Services\ModuleConfig;
use App\Helpers\Request;
use \Illuminate\Database\Capsule\Manager as Capsule;
use ReflectionException;
use function file_get_contents;
use function ob_end_clean;
use function ob_get_clean;
use function set_exception_handler;
use function strpos;

class Core
{
	private $capsule;
	private $request;
	private $config;
	private $data;

	/**
	 * @var Controller[]
	 */
	private $modules;

	/**
	 * @var Controller[][]
	 */
	private $eventSubscribers;

	public function __construct()
	{
		set_exception_handler(array($this, 'exceptionHandler'));

		$this->data = [];

		$this->config = new ModuleConfig(__DIR__);
		$this->initModules();

		$this->request = Request::resolve($_GET['request']);

		$this->capsule = new Capsule();
		$this->capsule->addConnection([
			'driver' => $this->cfg('db_driver'),
			'host' => $this->cfg('db_host'),
			'database' => $this->cfg('db_name'),
			'username' => $this->cfg('db_user'),
			'password' => $this->cfg('db_pass'),
			'charset' => $this->cfg('db_charset'),
			'collation' => $this->cfg('db_collation'),
			'prefix' => $this->cfg('db_prefix')
		]);

		$this->capsule->setAsGlobal();
		$this->capsule->bootEloquent();
		$this->callEvent('AfterStartCore');
	}

	/**
	 * Core entry point
	 * @throws ReflectionException
	 */
	public function run()
	{
		$this->request->moduleName =  $this->request->moduleName ?: $this->cfg('defaultModule');

		$this->callEvent('BeforeGlobalAction', array_merge($_GET, $_POST));
		$output = $this->runModule(
			$this->modules[$this->request->moduleName]
		);
		$this->callEvent('AfterGlobalAction', array_merge($_GET, $_POST), $output);
		echo $output;
	}

	/**
	 * @param Controller $module
	 * @return false|string
	 * @throws ReflectionException
	 */
	private function runModule(Controller $module)
	{
		return $module->run($this, array_merge($_GET, $_POST));
	}

	/**
	 * @param $moduleName
	 * @param $actionName
	 * @param array $params
	 * @return false|string
	 * @throws ReflectionException
	 */
	public function callModule($moduleName, $actionName, array $params = [])
	{
		if (isset($this->modules[$moduleName]) &&
				in_array($actionName, $this->modules[$moduleName]->listActions())
		) {
			return $this->modules[$moduleName]->runAction($actionName, $params);
		}
		return null;
	}

	/**
	 * Возвращает значение конфига
	 * @param $cfgName
	 * @return mixed
	 */
	protected function cfg($cfgName)
	{
		return $this->config->cfg($cfgName);
	}

	/**
	 * Обработчик неотловленных исключений
	 * @param $exception
	 */
	public final function exceptionHandler($exception)
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');

		ob_end_clean();

		if ($this->cfg('debug_mode')) {
			$this->data = $exception;
			echo $this->compileTemplate(__DIR__ . '/templates/Exception.tpl');
		}
		die;
	}

	/**
	 * Генерирует html из шаблона
	 * @param $filename
	 * @param $data
	 * @return false|string
	 */
	public function compileTemplate($filename, $data)
	{
		$oldData = $this->data;
		$this->data = $data;
		ob_start();
		eval('?>' . file_get_contents($filename) . '<?');
		$this->data = $oldData;
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function getRequest()
	{
		return $this->request;
	}

	private function resPath()
	{
		return $this->getPublicPath(__DIR__ . '/public');
	}

	/**
	 * Преобразует абсолютный путь в публичный
	 * @param $absPath
	 * @return bool|mixed|string
	 */
	public function getPublicPath($absPath)
	{
		if (strpos($absPath, $_SERVER['DOCUMENT_ROOT']) !== 0) return false;
		$result = str_replace($_SERVER['DOCUMENT_ROOT'], '', $absPath);

		if (strpos($result, '/') !== 0) $result = '/' . $result;

		return $result;
	}

	/**
	 * Возвращает список установленных модулей
	 *
	 * @return array
	 */
	private function listModules() : array
	{
		$modulesDir = $_SERVER['DOCUMENT_ROOT'] . '/Modules/';
		$dir_handle = opendir($modulesDir);

		$modules = [];
		while ($dir = readdir($dir_handle)) {
			if ($dir != "." && $dir != "..") {
				if (
					file_exists($modulesDir . $dir . '/config.json') &&
					file_exists($modulesDir . $dir . '/Controller.php')
				) {
					$modules[] = $dir;
				}
			}

		}

		return $modules;
	}

	/**
	 * Инициализирует модули
	 */
	public function initModules() {
		foreach ($this->listModules() as $moduleName) {
			$modulePath = "\\Modules\\{$moduleName}\\Controller";
			$this->modules[$moduleName] = new $modulePath($this);

			if (in_array('App\\ModuleInterface', class_implements($this->modules[$moduleName]))) {
				foreach ($this->modules[$moduleName]->listEvents() as $event) {
					$this->eventSubscribers[$event][] = $this->modules[$moduleName];
				}
			}
		}
	}

	public function callEvent($event, $params = [], &$hook = null) {
		if (!empty($this->eventSubscribers[$event])) {
			foreach ($this->eventSubscribers[$event] as $module) {
				$module->runEvent($event, $params, $hook);
			}
		}
	}
}