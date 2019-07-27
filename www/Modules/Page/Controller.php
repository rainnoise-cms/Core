<?php
namespace Modules\Page;

use \App\BaseController;
use App\Core;
use ReflectionException;

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

	public function event_AfterStartCore($params, Core $core, &$hook) {
		echo 'Event!';
	}
}