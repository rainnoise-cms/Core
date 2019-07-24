<?php
namespace Modules\Page;

use \App\BaseController;

class Controller extends BaseController {
	public function __construct(\App\Core $core){
		parent::__construct($core);
		
	}

	public function action_index($params) {
		echo "<pre>";
		print_r($params);
		throw new \RuntimeException("Да похуй!");
	}
	
	public function test(){
		$this->cfg('fh');
	}
}