<?php
/**
 * @Creator Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Author: Alexey Kutuzov <lexus27.khv@gmai.com>
 * @Project: ceive.data-attribute-locator
 */

namespace Ceive\Data\AttributeLocator\Exception;

use Ceive\Data\AttributeLocator\Exception;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class FoundException
 * @package Ceive\Data\AttributeLocator\Exception
 */
class FoundException extends Exception{
	
	public $root;
	
	public $path;
	
	public $value;
	
	/**
	 * MissingException constructor.
	 * @param mixed $startContainer
	 * @param string $startPath
	 * @param mixed $value
	 * container.{missingKey} is missing
	 */
	public function __construct($startContainer, $startPath, $value){
		
		$this->path     = $startPath;
		$this->root     = $startContainer;
		$this->value    = $value;
		
		parent::__construct("FoundException: {root}.{$startPath}, is exists");
	}
	
}


