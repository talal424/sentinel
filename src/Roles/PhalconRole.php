<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel
 * @version    2.0.13
 * @author     Cartalyst LLC
 * @author     Talal Alenizi <talal.alenizi@gmail.com>
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Roles;

use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Permissions\PermissibleTrait;
use \Phalcon\Mvc\Model;

class PhalconRole extends Model implements RoleInterface, PermissibleInterface
{
    use PermissibleTrait;

    /**
     * The Phalcon role_users model name.
     *
     * @var string
     */
    protected static $roleUsersModel = 'Cartalyst\Sentinel\Roles\PhalconRoleUsers';

    /**
     * The Phalcon users model name.
     *
     * @var string
     */
    protected static $usersModel = 'Cartalyst\Sentinel\Users\PhalconUser';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $slug;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $permissions;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // set dynamic update on
        $this->useDynamicUpdate(true);
        // relationship with role_users model
        $this->hasMany('id', static::$roleUsersModel, 'role_id', ['alias' => 'roleUsers']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->table;
    }

    /**
     * Sets table name mapped in the model.
     *
     * @param string $table
     * @return $this
     */
    public function setSource($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Roles[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Roles
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Remove role assignment to users
     */
    public function beforeDelete()
    {
        $this->roleUsers->delete();
    }

    /**
     * The role_users relationship.
     *
     * @return \Cartalyst\Sentinel\Roles\PhalconRoleUsers
     */
    public function users()
    {
        return (new static::$roleUsersModel)->setRoleId($this->getRoleId());
    }

    /**
     * Get mutator for the "permissions" attribute after fetch.
     *
     * @return void
     */
    public function afterFetch()
    {
        $this->permissions = $this->permissions ? json_decode($this->permissions, true) : [];
    }

    /**
     * Set mutator for the "permissions" attribute before save.
     *
     * @return void
     */
    public function beforeSave()
    {
        $this->permissions = $this->permissions ? json_encode($this->permissions) : '';
    }

    /**
     * Returns the role's primary key.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->getKey();
    }

    /**
     * Returns the role's slug.
     *
     * @return string
     */
    public function getRoleSlug()
    {
        return $this->slug;
    }

    /**
     * Returns all users for the role.
     *
     * @return \IteratorAggregate
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Returns the role_users model.
     *
     * @return string
     */
    public static function getRoleUsersModel()
    {
        return static::$roleUsersModel;
    }

    /**
     * Sets the role_users model.
     *
     * @param  string  $roleUsersModel
     * @return void
     */
    public static function setRoleUsersModel($roleUsersModel)
    {
        static::$roleUsersModel = $roleUsersModel;
    }

    /**
     * Returns the users model.
     *
     * @return string
     */
    public static function getUsersModel()
    {
        return static::$usersModel;
    }
    
    /**
     * Sets the users model.
     *
     * @param  string  $usersModel
     * @return void
     */
    public static function setUsersModel($usersModel)
    {
        static::$usersModel = $usersModel;
    }

    /**
     * Dynamically pass missing methods to the permissions.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $methods = ['hasAccess', 'hasAnyAccess'];

        if (in_array($method, $methods)) {
            $permissions = $this->getPermissionsInstance();

            return call_user_func_array([$permissions, $method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Creates the permissions object.
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
     */
    protected function createPermissions()
    {
        return new static::$permissionsClass($this->permissions);
    }

    /**
     * Returns the model's primary key.
     *
     * @return int
     */
    public function getKey()
    {
        $key = $this->getModelsMetaData()->getIdentityField($this);
        return $this->{$key};
    }
}
