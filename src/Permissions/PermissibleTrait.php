<?php namespace Cartalyst\Sentinel\Permissions;
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

trait PermissibleTrait {

	/**
	 * Cached permissions instance for the given user.
	 *
	 * @var \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	protected $permissionsInstance;

	/**
	 * The permissions instance class.
	 *
	 * @var string
	 */
	protected static $permissionsClass = 'Cartalyst\Sentinel\Permissions\StrictPermissions';

	/**
	 * Get permissions.
	 *
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * Set permissions.
	 *
	 * @param  array  $permissions
	 * @return void
	 */
	public function setPermissions(array $permissions)
	{
		$this->permissions = $permissions;
	}

	/**
	 * Get the permissions class.
	 *
	 * @return string
	 */
	public static function getPermissionsClass()
	{
		return static::$permissionsClass;
	}

	/**
	 * Set the permissions class.
	 *
	 * @param  string  $permissionsClass
	 * @return void
	 */
	public static function setPermissionsClass($permissionsClass)
	{
		static::$permissionsClass = $permissionsClass;
	}

	/**
	 * Creates a permissions object.
	 *
	 * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
	 */
	abstract function createPermissions();

	/**
	 * {@inheritDoc}
	 */
	public function getPermissionsInstance()
	{
		if ($this->permissionsInstance === null)
		{
			$this->permissionsInstance = $this->createPermissions();
		}

		return $this->permissionsInstance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addPermission($permission, $value = true)
	{
		if ( ! array_key_exists($permission, $this->permissions))
		{
			$this->permissions = array_merge($this->permissions, [$permission => $value]);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function updatePermission($permission, $value = true)
	{
		if (array_key_exists($permission, $this->permissions))
		{
			$permissions = $this->permissions;

			$permissions[$permission] = $value;

			$this->permissions = $permissions;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removePermission($permission)
	{
		if (array_key_exists($permission, $this->permissions))
		{
			$permissions = $this->permissions;

			unset($permissions[$permission]);

			$this->permissions = $permissions;
		}

		return $this;
	}

}