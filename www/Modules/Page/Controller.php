<?php
namespace Modules\Page;

use \App\BaseController;
use App\Core;
use ReflectionException;
use RuntimeException;

class Controller extends BaseController {
    /**
     * Controller constructor.
     * @param Core $core
     * @throws ReflectionException
     */
    public function __construct(Core $core){
		parent::__construct($core);
		
	}

	public function action_index($params) {
		echo "It works!";
	}
}