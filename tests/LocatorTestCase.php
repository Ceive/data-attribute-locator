<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.data-attribute-context
 */

namespace Ceive\Data\AttributeLocator\Tests;

use Ceive\Data\AttributeLocator\Exception\MissingException;
use Ceive\Data\AttributeLocator\Locator;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class LocatorTestCase
 * @package Ceive\Data\AttributeContext\Tests
 */
class LocatorTestCase extends \PHPUnit_Framework_TestCase{
	
	/** @var Locator  */
	public $locator;
	
	public $array;
	public $object;
	
	public $array_mixed;
	public $object_mixed;
	
	public function setUp(){
		$this->locator = new Locator();
		
		$this->object                      = new \stdClass();
		$this->object->user                = new \stdClass();
		$this->object->user->profile       = new \stdClass();
		$this->object->user->profile->name = 'Lesha';
		
		
		$this->array = [
			'user' => [
				'profile' => [
					'contacts' => [
						'a',
						'b',
						'c',
						'd'
					],
				]
			]
		];
		
		
		
		$this->object_mixed = clone $this->object;
		$this->object_mixed->array = $this->array;
		
		$this->array_mixed = $this->array;
		$this->array_mixed['object'] = $this->object;
		
	}
	
	
	
	public function testElapsedPath(){
		$locator = $this->locator;
		
		$value = $locator->pathElapsed('user.profile.name','profile.name');
		$this->assertEquals(['user'], $value);
		
		$value = $locator->pathElapsed('user.profile.name',null,2);
		$this->assertEquals(['user','profile'], $value);
	}
	
	/**
	 *
	 */
	public function testQuery(){
		$locator = $this->locator;
		
		// Query from {object} to path to notExisting attribute
		
		$locator->throwOnMissing = false;
		$value = $locator->query($this->object, 'user.profile.not_presence');
		$this->assertEquals(null, $value);
		//$locator->throwOnMissing = true;
		
		
		// Query from {object} to path
		$value = $locator->query($this->object, 'user.profile.name');
		$this->assertEquals('Lesha', $value);
		
		// Query from {string} to extra without path
		$value = $locator->query('Lesha', ':type');
		$this->assertEquals('string', $value);
		
		// Query from {string} to extra without path
		$value = $locator->query('Lesha', ':length');
		$this->assertEquals(5, $value);
		
		// Query from {string} to extra without path
		$value = $locator->query('Алексей', ':length-mb');
		$this->assertEquals(7, $value);
		
		// Query from {string} to path
		$value = $locator->query('another string', 'user.profile');
		$this->assertEquals(null,$value);
		
		// Query from {string} to path
		$value = $locator->query($this->array, 'user.profile.contacts:first');
		$this->assertEquals('a', $value);
		
		$value = $locator->query($this->array, 'user.profile.contacts.0');
		$this->assertEquals('a', $value);
		
		
		$value = $locator->query($this->array, 'user.profile.contacts:last');
		$this->assertEquals('d', $value);
		
		$value = $locator->query($this->array, 'user.profile.contacts.-1');
		$this->assertEquals('d', $value);
		
		
		
		
		$value = $locator->query($this->array, 'user.profile.0');//contacts
		$this->assertEquals([ 'a', 'b', 'c', 'd' ], $value);
		
		$value = $locator->query($this->array, 'user.profile.-1');//contacts
		$this->assertEquals([ 'a', 'b', 'c', 'd' ], $value);
		
		
		$locator->query(['a'],'0');
		
		
	}
	
}


