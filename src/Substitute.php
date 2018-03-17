<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 19.02.2016
 * Time: 16:45
 */
namespace Ceive\Data\AttributeLocator {
	
	/**
	 *
	 * Класс реализует подмену целевого объекта контекста.
	 * Применим для:
	 *  Подмена свойств или типов самого объекта при проверках до непосредственного доступа к реальному объекту
	 *  сбор Query из Conditions|Expressions при проверках до непосредственного доступа к реальному объекту
	 *
	 *
	 * Class Substitute
	 * @package Ceive\Abac\Context\Context
	 */
	class Substitute implements SubstituteInterface{

		/** @var mixed value if not substitute */
		protected $value;

		/** @var bool value defined */
		protected $value_defined = false;

		/** @var array  */
		protected $extra = [];

		/**
		 * @param $class_name
		 * @return $this
		 */
		public function setClass($class_name){
			$this->setExtra('class', $class_name);
			return $this;
		}

		/**
		 * @return string|null
		 */
		public function getClass(){
			return $this->value_defined && is_object($this->value)?get_class($this->value):$this->getExtra('class');
		}


		/**
		 * @param $var_type
		 * @return $this
		 */
		public function setType($var_type){
			$this->setExtra('type', $var_type);
			return $this;
		}

		/**
		 * @return string|null
		 */
		public function getType(){
			return $this->value_defined?gettype($this->value):$this->getExtra('type');
		}


		/**
		 * @param $count
		 * @return $this
		 */
		public function setCount($count){
			$this->setExtra('count', $count);
			return $this;
		}

		/**
		 * @return int|null
		 */
		public function getCount(){
			return $this->value_defined?count($this->value):$this->getExtra('count');
		}


		/**
		 * @param $length
		 * @return $this
		 */
		public function setLength($length){
			$this->setExtra('length', $length);
			return $this;
		}

		/**
		 * @return int|null
		 */
		public function getLength(){
			return $this->value_defined?strlen($this->value):$this->getExtra('length');
		}

		/**
		 * @param $value
		 * @return $this
		 */
		public function setValue($value){
			$this->value = $value;
			$this->value_defined = true;
			return $this;
		}

		/**
		 * @return string
		 */
		public function getValue(){
			return $this->value;
		}

		/**
		 * @return bool
		 */
		public function eraseValue(){
			$this->value_defined = false;
			$this->value         = null;
			return true;
		}
		
		/**
		 * @return bool
		 */
		public function isDefined(){
			return $this->value_defined;
		}
		
		
		public function getExtra($extra, &$found = false){
			switch($extra){
				case 'class':
					$found = true;
					return $this->getClass();
					break;
				case 'type':
					$found = true;
					return $this->getType();
					break;
				case 'count':
					$found = true;
					return $this->getCount();
					break;
				case 'length':
					$found = true;
					return $this->getLength();
					break;
			}
			
			if(isset($this->extra[$extra])){
				$found = true;
				return $this->extra[$extra];
			}
			$found = false;
			return null;
		}
		
		public function setExtra($extra, $value){
			$this->extra[$extra] = $value;
			return $this;
		}
		
		/**
		 * @param array $info
		 * @param null $value
		 * @return static
		 */
		public static function release(array $info, $value=null){
			$s = new static();
			$s->extra = $info;
			if($value!==null)$s->setValue($value);
			return $s;
		}
		
		public function modify($modifier, &$found){
			
			$found = false;
			
			return null;
			
		}
		
	}
}

