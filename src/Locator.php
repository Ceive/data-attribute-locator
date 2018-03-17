<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.data-attribute-context
 */

namespace Ceive\Data\AttributeLocator;
use Ceive\Data\AttributeLocator\Exception\CatchableException;
use Ceive\Data\AttributeLocator\Exception\ContinueException;
use Ceive\Data\AttributeLocator\Exception\FoundException;
use Ceive\Data\AttributeLocator\Exception\MissingException;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class Locator
 * @package Ceive\Data\AttributeLocator
 */
class Locator{
	
	public $throwOnMissing = true;
	
	protected $pathDelimiter = '.';
	
	protected $modifierSeparator = ':';
	
	protected $callOpen = '(';
	protected $callClose = ')';
	protected $callArgsSeparator = ',';
	
	
	
	
	public function getPathDelimiter(){
		return $this->pathDelimiter;
	}
	
	public function getModifierSeparator(){
		return $this->modifierSeparator;
	}
	
	public function getCallIdentifiers(){
		return [$this->callOpen, $this->callArgsSeparator, $this->callClose];
	}
	
	protected $store = [];
	
	protected $startContainer;
	
	protected $startPath;
	
	protected $elapsedDepth;
	
	protected $elapsedPath;
	
	protected $container;
	protected $path;
	protected $nextPath;
	protected $key;
	protected $modifiers;
	
	
	
	
	
	protected $value;
	
	protected $result;
	
	/**
	 * todo: Пути - они же лексерные выражения, они же могут являться вызовами методов call() path
	 * todo: Substitution, and ObjectAccessor phantom object
	 * todo: AutocompleteInterface
	 * todo: Substitution or Custom LocationBehaviour
	 * @param $container
	 * @param $path
	 * @param array $meta
	 * @return mixed
	 */
	public function query($container, $path, array $meta = null ){
		
		if($meta===null){
			$path = $this->normalizePath($path);
			$meta = [0, $container, $path];
		}else{
			$path = $this->decompositePath($path);
		}
		
		list($depth, $startContainer, $startPath) = $meta;
		
		$this->startContainer = $startContainer;
		$this->startPath      = $startPath;
		
		$this->elapsedDepth = $depth;
		$this->elapsedPath  = $this->pathElapsed($startPath, null, $depth);
		
		$this->container    = $container;
		$this->path         = $path;
		
		// ~~reset
		$this->nextPath
			= $this->key
			= $this->modifiers
			= $this->value
			= $this->result
			
			= null;
		// reset~~
		
		try{
			return $this->getFrom($this->container, $this->path);
		}catch(FoundException $e){
			return $e->value;
		}catch(CatchableException $e){
			
		}catch(ContinueException $e){
			
		}
		
		//elapse increment
		$segment = array_shift($path);// path is right(remains path)
		$this->nextPath = $path;
		
		list($key, $modifiers, $arguments) = $this->parseSegment($segment);
		
		$this->key = $key;
		$this->modifiers = $modifiers;
		
		if($key){
			try{
				$this->beforeGet();
				$this->value = $this->getFrom($this->container, $this->key);
			}catch(CatchableException $e){
				return $this->onNotFound();
			}catch(FoundException $e){
				$this->value = $e->value;
			}catch(ContinueException $e){}
		}else{
			$this->value = $container;
		}
		
		$this->onFound();
		
		$this->result = $this->value;
		
		if($this->modifiers){
			try{
				$this->result = $this->resolveModifiers($this->value);
			}catch(FoundException $e){
				$this->result = $e->value;
			}catch(ContinueException $e){}
			$this->onModifiers();
		}else{
			$this->result = $this->value;
		}
		
		$this->onResult();
		
		// RETURN || NEXT QUERY
		if($this->nextPath){
			// NEXT QUERY
			
			return $this->query($this->result, $this->nextPath, [$this->elapsedDepth+1, $this->startContainer, $this->startPath] );
		}else{
			// RETURN
			return $this->result;
		}
	}
	
	
	/**
	 * @param $container
	 * @param $key
	 * @return mixed
	 * @throws CatchableException
	 */
	public function getFrom($container, $key){
		$key = $this->pathString($key);
		return $this->resolveValueAccess($container, $key);
	}
	
	/**
	 * @param $container
	 * @param $key
	 * @return mixed
	 * @throws CatchableException
	 */
	public function resolveValueAccess($container, $key){
		
		if(is_object($container)){
			
			if(property_exists($container,$key)){
				return $container->{$key};
			}
			
			if(method_exists($container,'__get') && isset($container->{$key})){
				return $container->{$key};
			}
			
			if($container instanceof \ArrayAccess && isset($container[$key])){
				return $container[$key];
			}
		}
		
		if(is_array($container)){
			if(array_key_exists($key, $container)){
				return $container[$key];
			}elseif(is_numeric($key)){
				$a = array_slice($container, intval($key), 1);
				if($a){
					return array_shift($a);
				}
			}
		}
		
		//notFound
		throw CatchableException::get();
	}
	
	
	/**
	 * @param $value
	 * @return int|string
	 */
	public function resolveModifiers($value){
		foreach($this->modifiers as $modifier){
			try{
				$value = $this->beforeModifier($value, $modifier);
				$value = $this->applyModifier($value, $modifier);
			}catch(FoundException $e){
				$value = $e->value;
			}
		}
		return $value;
	}
	
	public function applyModifier($value, $modifier){
		switch($modifier){
			case 'class':
				$value = is_object($value)? get_class($value) : null;
				break;
			case 'type':
				$value = gettype($value);
				break;
			case 'count':
				$value = $value instanceof \Countable || is_array($value)? count($value) : null;
				break;
			
			case 'bytes':
			case 'length':
				$value = is_string($value)? strlen($value) : null;
				break;
			
			case 'length-mb':
			case 'length-utf':
			case 'length-multi-byte':
				$value = is_string($value)? mb_strlen($value) : null;
				break;
			
			case 'rand':
				$value = rand();
				break;
			
			
			
			case 'first-item':
				$value = is_array($value) && $value? array_slice($value,0,1,false)[0] : null;
				break;
			case 'last-item':
				$value = is_array($value) && $value? array_slice($value,-1,1,false)[0] : null;
				break;
			
			case 'first-char':
				$value = is_scalar($value) && $value? mb_substr($value,0,1) : null;
				break;
			case 'last-char':
				$value = is_scalar($value) && $value? mb_substr($value,-1,1) : null;
				break;
			
			case 'first':
				if($value){
					if(is_array($value)) return array_slice($value,0,1,false)[0];
					else                 return mb_substr($value,0,1);
					
				}else $value = null;
				break;
			case 'last':
				if($value){
					if(is_array($value)) return array_slice($value,-1,1,false)[0];
					else                 return mb_substr($value,-1,1);
				}else $value = null;
				break;
		}
		return $value;
	}
	
	
	public function pathString($path){
		return (is_array($path)?implode($this->getPathDelimiter(),$path):$path);
	}
	
	/***
	 * @param $path
	 * @return array
	 */
	public function decompositePath($path){
		if(!is_array($path)){
			$path = array_diff(explode($this->getPathDelimiter(),$path),[null,'']);
		}
		return $path;
	}
	
	/**
	 * @param $path
	 * @return array
	 */
	public function normalizePath($path){
		$pd = $this->getPathDelimiter();
		if(!is_array($path)){
			$path = array_diff(explode($pd,$path),[null,'']);
		}else{
			$a = [];
			foreach($path as $c){
				if(is_array($c)){
					$a = array_merge($a, $c);
				}elseif(is_string($c) && strpos($c,$pd)!==false){
					$a = array_merge($a, array_diff(explode($pd,$path),[null,'']));
				}
			}
			$path = $a;
		}
		return $path;
	}
	
	/**
	 * @param $segment
	 * @return array [path, extra]
	 */
	public function parseSegment($segment){
		$ms = $this->getModifierSeparator();
		if(strpos($segment, $ms) !== false){
			list($key, $modifiers) = array_replace([null,null],explode( $ms , $segment,2));
			$modifiers = $modifiers? array_filter(explode($ms, $modifiers)) : [] ;
			return [$key?:null, $modifiers?:[], null];
		}
		return [$segment,null, null];
	}
	
	/**
	 * @param $start_path
	 * @param $ahead_path
	 * @param null $depth
	 * @return array
	 */
	public function pathElapsed($start_path, $ahead_path = null, $depth = null){
		
		if(!is_null($depth)){
			$start_path = $this->decompositePath($start_path);
			return array_slice($start_path, 0 , $depth);
		}
		
		if(!is_null($ahead_path)){
			$ahead_path = $this->decompositePath($ahead_path);
			$start_path = $this->decompositePath($start_path);
			return array_diff($start_path, $ahead_path);
		}
		
		return null;
	}
	
	/**
	 * @see $container
	 * @see $key
	 * @see ContinueException
	 */
	protected function beforeGet(){
		
		if($this->container instanceof SubstituteInterface && $this->container->isDefined()){
			$this->container = $this->container->getValue();
		}
		
	}
	
	/**
	 * @param $value
	 * @param $modifier
	 * @return mixed
	 * @throws FoundException
	 */
	protected function beforeModifier($value, $modifier){
		if($value instanceof Substitute){
			$v = $value->modify($modifier, $found);
			if($found){
				throw new FoundException($this->startContainer, $this->startPath, $v);
			}else if($value->isDefined()){
				$value = $value->getValue();
			}
		}
		return $value;
	}
	
	
	
	
	
	/**
	 * @throws MissingException
	 * @see $startContainer     Context{}
	 * @see $startPath          ["user", "profile:length", "fullname"]
	 * @see $elapsedPath        ~ ["user"]
	 * @see $elapsedDepth       1
	 * @see $container          ~ User{}
	 * @see $path               ["profile:length","fullname"]
	 * @see $key                ~ "profile"
	 * @see $modifiers          ["length"]
	 * @see $nextPath           ["fullname"]
	 * @see $value              NULL
	 * @see $result             NULL
	 */
	protected function onNotFound(){
		if(!$this->throwOnMissing){
			return null;
		}
		
		
		throw new MissingException(
			$this->startContainer,
			$this->pathString($this->startPath),
			
			$this->pathString($this->elapsedPath),
			$this->container,
			$this->key
		);
		
	}
	
	/**
	 * @see $startContainer     Context{}
	 * @see $startPath          ["user", "profile:length", "fullname"]
	 * @see $elapsedPath        ["user"]
	 * @see $elapsedDepth       1
	 * @see $container          ~ User{}
	 * @see $path               ["profile:length","fullname"]
	 * @see $key                ~ "profile"
	 * @see $modifiers          ["length"]
	 * @see $nextPath           ["fullname"]
	 * @see $value              ~ "babka"
	 * @see $result             NULL
	 *
	 */
	protected function onFound(){
		
	}
	
	/**
	 * @see $startContainer     Context{}
	 * @see $startPath          ["user", "profile:length", "fullname"]
	 * @see $elapsedPath        ["user"]
	 * @see $elapsedDepth       1
	 * @see $container          User{}
	 * @see $path               ["profile:length","fullname"]
	 * @see $key                "profile"
	 * @see $modifiers          ["length"]
	 * @see $nextPath           ["fullname"]
	 * @see $value              "babka"
	 * @see $result             ~ 5
	 */
	protected function onModifiers(){
		
	}
	
	
	/**
	 * @see $startContainer     Context{}
	 * @see $startPath          ["user", "profile:length", "fullname"]
	 * @see $elapsedPath        ["user"]
	 * @see $elapsedDepth       1
	 * @see $container          User{}
	 * @see $path               ["profile:length","fullname"]
	 * @see $key                "profile"
	 * @see $modifiers          ["length"]
	 * @see $nextPath           ["fullname"]
	 * @see $value              "babka"
	 * @see $result             ~ 5
	 *
	 * @note from value number (5) could not be found property "fullname" in next recursion nextPath
	 *
	 * Next variants:
	 *   // RETURN || NEXT QUERY
	 *   if($this->nextPath){
	 *   	// NEXT QUERY
	 *   }else{
	 *   	// RETURN
	 *   }
	 */
	protected function onResult(){
		
		if($this->nextPath){
			
		}else{
			
		}
		
	}
	
	
	
	
}


