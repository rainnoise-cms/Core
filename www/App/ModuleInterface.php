<?php


namespace App;


interface ModuleInterface
{
	public function __construct(Core $core);

	public function listEvents();

	public function listActions();

	public function run();
}