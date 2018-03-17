<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.data-attribute-context
 */

namespace Ceive\Data\AttributeLocator\Exception;

use Ceive\Data\AttributeLocator\Exception;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class MissingException
 * @package Ceive\Data\AttributeLocator
 */
class MissingException extends Exception{
	
	public $root;
	
	public $startPath;
	
	public $container;
	
	public $elapsedPath;
	
	public $key;
	
	/**
	 * MissingException constructor.
	 * @param string $startContainer
	 * @param string $startPath
	 * @param \Exception $elapsedPath
	 * @param int $container
	 * @param $key
	 *
	 *
	 * container.{missingKey} is missing
	 */
	public function __construct($startContainer, $startPath, $elapsedPath, $container, $key){
		
		$this->root = $startContainer;
		$this->startPath = $startPath;
		$this->elapsedPath = $elapsedPath;
		$this->container = $container;
		$this->key = $key;
		
		$target = ['{root}',$elapsedPath];
		$target = array_filter($target);
		$target = implode('.',$target);
		
		$start = ['{root}',$startPath];
		$start = array_filter($start);
		$start = implode('.',$start);
		
		parent::__construct("Not found: {$start}; Missing '{$key}' in {$target}");
	}
	
}


