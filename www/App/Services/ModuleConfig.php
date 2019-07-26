<?php
namespace App\Services;



use RuntimeException;

define('CONFIGS_DIR', '/Configs/');

/**
 * Управляет настройками модулей
 *
 * Class Config
 * @package App\Services
 */
class ModuleConfig
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $author;

	/**
	 * @var string
	 */
	private $licence;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $date;

	/**
	 * @var ConfigString[]
	 */
	private $config;

	/**
	 * Configurator constructor.
	 * @param $modulePath
	 */
	public function __construct($modulePath)
	{
		$meta = json_decode(file_get_contents($modulePath . '/config.json'), true);

		if (empty($meta)) {
			throw new RuntimeException("File not found $modulePath/config.json");
		}

		$this->name = $meta['name'];
		$this->author = $meta['author'];
		$this->version = $meta['version'];
		$this->date = $meta['date'];
		$this->licence = $meta['licence'];
		$this->description = $meta['description'];

		$config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . CONFIGS_DIR . $this->name . '.json'), true);

		foreach ($meta['cfg'] as $key => $data) {
			$data['value'] = $data['default'];
			if (isset($config[$key])) {
				$data['value'] = $config[$key];
			}

			$this->config[$key] = new ConfigString($key, $data);
		}

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getLicence()
	{
		return $this->licence;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function cfg($key)
	{
		return $this->config[$key]->getValue();
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function cfgSet($key, $value) {
		$this->config[$key]->setValue($value);
	}

	public function save()
	{
		$configs = [];
		foreach ($this->config as $key => $cfg) {
			$configs[$key] = $cfg->getValue();
		}

		if (!file_put_contents(
			$_SERVER['DOCUMENT_ROOT'] . CONFIGS_DIR . $this->name . '.json',
			json_encode($configs)
		)) {
			throw new RuntimeException("Cannot write '{$this->name}' config");
		}
	}
}