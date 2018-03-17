<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 19.02.2016
 * Time: 16:45
 */
namespace Ceive\Data\AttributeLocator {
	
	/**
	 * Interface SubstituteInterface
	 * @package Ceive\Abac\Context\Context
	 */
	interface SubstituteInterface{

		/**
		 * @param $class_name
		 * @return mixed
		 */
		public function setClass($class_name);
		public function getClass();


		/**
		 * @param $var_type
		 * @return mixed
		 */
		public function setType($var_type);
		public function getType();


		/**
		 * @param $count
		 * @return mixed
		 */
		public function setCount($count);
		public function getCount();

		/**
		 * @param $length
		 * @return mixed
		 */
		public function setLength($length);
		public function getLength();
		/**
		 * @return bool
		 */
		public function isDefined();
		
		
		public function getValue();
		
		public function eraseValue();
		
		public function setValue($value);
		
	}
}

