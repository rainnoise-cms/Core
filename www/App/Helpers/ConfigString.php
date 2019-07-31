<?php


namespace App\Helpers;


class ConfigString
{
	private $key;
	private $type;
	private $variants;
	private $value;

	public function __construct($key, array $config)
	{
		$this->key = $key;
		$this->type = $config['type'];
		$this->variants = $config['variants'];
		$this->value = $config['value'];
	}

	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
}