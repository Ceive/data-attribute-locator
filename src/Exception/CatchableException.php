<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.data-attribute-context
 */

namespace Ceive\Data\AttributeLocator\Exception;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class CatchableException
 * @package Ceive\Data\AttributeLocator
 */
class CatchableException extends \Exception{
	
	protected static $exception;
	
	public static function get(){
		if(!static::$exception){
			static::$exception = new static();
		}
		return static::$exception;
	}
}


