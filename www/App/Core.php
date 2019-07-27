<?php

namespace App;

use App\Services\ModuleConfig;
use function file_get_contents;
use function ob_end_clean;
use function ob_get_clean;
use function set_exception_handler;
use function strpos;

class Core
{
	private $params;
	private $request;
	private $config;
	private $data;

	/**
	 * @var BaseController[]
	 */
	private $modules;

	private $eventSubscribers;

	public function __construct()
	{
		set_exception_handler(array($this, 'exceptionHandler'));
		$this->data = [];

		$this->config = new ModuleConfig(__DIR__);
		$this->initModules();


		$this->CallEvent('AfterStartCore');
		// Constructs request data
		$_buffer = explode('/', $_GET['request']);

		$this->request = [
			'module' => @$_buffer[0],
			'action' => @$_buffer[1],
		];

		if (@$_buffer[2]) {
			$this->params = [
				'id' => @$_buffer[2]
			];
		}
	}

	/**
	 * Core entry point
	 */
	public function run()
	{
		$moduleName = $this->request['module'] ?: $this->cfg('defaultModule');

		$action = $this->params['request']['action'];
		$output = $this->runAction($this->modules[$moduleName], $action, $_GET + $_POST);
		echo $output;
	}

	private function runAction(BaseController $module, $action, array $params = [])
	{
		return $module->runAction($action, $this, $params);
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
	 * @return false|string
	 */
	public function compileTemplate($filename)
	{
		ob_start();
		eval('?>' . file_get_contents($filename) . '<?');
		return ob_get_clean();
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
				foreach ($this->modules[$moduleName]->getEvents() as $event) {
					$this->eventSubscribers[$event][] = $this->modules[$moduleName];
				}
			}
		}
	}

	public function CallEvent($event, $params = [], &$hook = null) {
		foreach ($this->eventSubscribers[$event] as $module) {
			if (method_exists($module, 'event_' . $event)) {
				$module->{'event_' . $event}($params, $this, $hook);
			}
		}
	}
}