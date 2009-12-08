<?php
/**
 * Elgg Test ElggUser
 *
 * @package Elgg
 * @subpackage Test
 * @author Curverider Ltd
 * @link http://elgg.org/
 */
class ElggCoreUserTest extends ElggCoreUnitTest {

	/**
	 * Called before each test object.
	 */
	public function __construct() {
		parent::__construct();
		
		// all code should come after here
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->user = new ElggUserTest();
	}

	/**
	 * Called after each test method.
	 */
	public function tearDown() {
		// do not allow SimpleTest to interpret Elgg notices as exceptions
		$this->swallowErrors();
		
		unset($this->user);
	}

	/**
	 * Called after each test object.
	 */
	public function __destruct() {
		// all code should go above here
		parent::__destruct();
	}

	/**
	 * A basic test that will be called and fail.
	 */
	public function testElggUserConstructor() {
		$attributes = array();
		$attributes['guid'] = '';
		$attributes['type'] = 'user';
		$attributes['subtype'] = '';
		$attributes['owner_guid'] = get_loggedin_userid();
		$attributes['container_guid'] = get_loggedin_userid();
		$attributes['site_guid'] = 0;
		$attributes['access_id'] = ACCESS_PRIVATE;
		$attributes['time_created'] = '';
		$attributes['time_updated'] = '';
		$attributes['enabled'] = 'yes';
		$attributes['tables_split'] = 2;
		$attributes['tables_loaded'] = 0;
		$attributes['name'] = '';
		$attributes['username'] = '';
		$attributes['password'] = '';
		$attributes['salt'] = '';
		$attributes['email'] = '';
		$attributes['language'] = '';
		$attributes['code'] = '';
		$attributes['banned'] = 'no';
		
		$this->assertIdentical($this->user->expose_attributes(), $attributes);
	}
	
	public function testElggUserLoad() {
		// new object
		$object = new ElggObject();
		$this->AssertEqual($object->getGUID(), 0);
		$guid = $object->save();
		$this->AssertNotEqual($guid, 0);
		
		// fail on wrong type
		try {
			$error = new ElggUserTest($guid);
			$this->assertTrue(FALSE);
		} catch (Exception $e) {
			$this->assertIsA($e, 'InvalidClassException');
			$message = sprintf(elgg_echo('InvalidClassException:NotValidElggStar'), $guid, 'ElggUser');
			$this->assertIdentical($e->getMessage(), $message);
		}
		
		// clean up
		$object->delete();
	}
	
	public function testElggUserConstructorByGuid() {
		$user = new ElggUser(get_loggedin_userid());
		$this->assertIdentical($user, $_SESSION['user']);
		
		// fail with garbage
		try {
			$error = new ElggUserTest(array('invalid'));
			$this->assertTrue(FALSE);
		} catch (Exception $e) {
			$this->assertIsA($e, 'InvalidParameterException');
			$message = sprintf(elgg_echo('InvalidParameterException:UnrecognisedValue'));
			$this->assertIdentical($e->getMessage(), $message);
		}
	}
	
	public function testElggUserConstructorByDbRow() {
		$row = $this->fetchUser(get_loggedin_userid());
		$user = new ElggUser($row);
		$this->assertIdentical($user, $_SESSION['user']);
	}
	
	public function testElggUserConstructorByUsername() {
		$row = $this->fetchUser(get_loggedin_userid());
		$user = new ElggUser($row->username);
		$this->assertIdentical($user, $_SESSION['user']);
	}
	
	public function testElggUserConstructorByObject() {
		$obj = new ElggUser(get_loggedin_userid());
		$user = new ElggUser($obj);
		$this->assertIdentical($obj, $user);
		$this->assertIdentical($user, $_SESSION['user']);
		
		// fail on non-user object
		$object = new ElggObject();
		$object->save();
		
		try {
			$error = new ElggUserTest($object);
			$this->assertTrue(FALSE);
		} catch (Exception $e) {
			$this->assertIsA($e, 'InvalidParameterException');
			$message = sprintf(elgg_echo('InvalidParameterException:NonElggUser'));
			$this->assertIdentical($e->getMessage(), $message);
		}
		
		$object->delete();
	}
	
	public function testElggUserSave() {
		// new object
		$this->AssertEqual($this->user->getGUID(), 0);
		$guid = $this->user->save();
		$this->AssertNotEqual($guid, 0);
		
		// clean up
		$this->user->delete();
	}
	
	public function testElggUserDelete() {
		$guid = $this->user->save();
		
		// delete object
		$this->assertTrue($this->user->delete());
		
		// check GUID not in database
		$this->assertFalse($this->fetchUser($guid));
	}
	
	public function testElggUserNameCache() {
		// Trac #1305
		
		// very unlikely a user would have this username
		$name = (string)time();
		$this->user->username = $name;
		
		$guid = $this->user->save();
		
		$user = get_user_by_username($name); 
		$user->delete(); 
		$user = get_user_by_username($name);
		$this->assertFalse($user);
	}
	
	protected function fetchUser($guid) {
		global $CONFIG;
		
		return get_data_row("SELECT * FROM {$CONFIG->dbprefix}users_entity WHERE guid = '$guid'");
	}
}

class ElggUserTest extends ElggUser {
	public function expose_attributes() {
		return $this->attributes;
	}
}
