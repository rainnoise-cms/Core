<?php

namespace App;

use App\Services\ModuleConfig;
use RuntimeException;
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

	private $modules;

	public function __construct()
	{
		set_exception_handler(array($this, 'exceptionHandler'));
		$this->data = [];

		$this->config = new ModuleConfig(__DIR__);

		// Constructs request data
		$_buffer = explode('/', $_GET['request']);


		$this->request = [
			'module' => @$_buffer[0],
			'action' => @$_buffer[1],
			'id' => @$_buffer[2]
		];
	}

	/**
	 * Core entry point
	 */
	public function run()
	{
		$moduleName = $this->request['module'] ?: $this->cfg('defaultModule');
		$modulePath = "\\Modules\\{$moduleName}\\Controller";

		if (!class_exists($modulePath)) {
			throw new RuntimeException("Module '$moduleName' does not exist");
		}

		$module = new $modulePath($this);

		$action = $this->params['request']['action'];
		$output = $this->runAction($module, $action, $_GET + $_POST);
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
		return $this->getPublicPath(__DIR__ . '/resources');
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

	public function getModuleName()
	{
		return 'Core';
	}

	public function initModule() {

	}
}