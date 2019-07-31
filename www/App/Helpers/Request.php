<?php


namespace App\Helpers;


class Request
{
	public $moduleName;
	public $actionName;

	public static function resolve(string $url) : Request
	{
		preg_match('#^/?(\w+)/([A-Za-z]\w+)#', $url, $matches);

		$request = new Request();
		$request->moduleName = $matches[1];
		$request->actionName = $matches[2];
		return $request;
	}
}