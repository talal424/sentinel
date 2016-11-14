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

namespace Cartalyst\Sentinel\Users;

use Cartalyst\Sentinel\Permissions\PermissibleInterface;
use Cartalyst\Sentinel\Permissions\PermissibleTrait;
use Cartalyst\Sentinel\Persistences\PersistableInterface;
use Cartalyst\Sentinel\Roles\RoleableInterface;
use Cartalyst\Sentinel\Roles\RoleInterface;
use \Phalcon\Mvc\Model;

class PhalconUser extends Model implements RoleableInterface, PermissibleInterface, PersistableInterface, UserInterface
{
    use PermissibleTrait;

    // phalcon model backbone
    use PhalconUserTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * Array of login column names.
     *
     * @var array
     */
    protected $loginNames = ['email'];

     /**
     * persistable key
     */
    protected $persistableKey = 'user_id';

    /**
     * persistable Relationship property
     */
    protected $persistableRelationship = 'persistences';

    /**
     * The Phalcon roles model name.
     *
     * @var string
     */
    protected static $rolesModel = 'Cartalyst\Sentinel\Roles\PhalconRole';

    /**
     * The Phalcon role_users model name.
     *
     * @var string
     */
    protected static $roleUsersModel = 'Cartalyst\Sentinel\Roles\PhalconRoleUsers';

    /**
     * The Phalcon persistences model name.
     *
     * @var string
     */
    protected static $persistencesModel = 'Cartalyst\Sentinel\Persistences\PhalconPersistence';

    /**
     * The Phalcon activations model name.
     *
     * @var string
     */
    protected static $activationsModel = 'Cartalyst\Sentinel\Activations\PhalconActivation';

    /**
     * The Phalcon reminders model name.
     *
     * @var string
     */
    protected static $remindersModel = 'Cartalyst\Sentinel\Reminders\PhalconReminder';

    /**
     * The Phalcon throttling model name.
     *
     * @var string
     */
    protected static $throttlingModel = 'Cartalyst\Sentinel\Throttling\PhalconThrottle';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // set dynamic update on
        $this->useDynamicUpdate(true);
        // relationship with activations model
        $this->hasMany(
            'id',
            static::$activationsModel,
            'user_id',
            ['alias' => 'activations']
        );
        // relationship with persistences model
        $this->hasMany(
            'id',
            static::$persistencesModel,
            'user_id',
            ['alias' => 'persistences']
        );
        // relationship with reminders model
        $this->hasMany(
            'id',
            static::$remindersModel,
            'user_id',
            ['alias' => 'reminders']
        );
        // relationship with throttle model
        $this->hasMany(
            'id',
            static::$throttlingModel,
            'user_id',
            ['alias' => 'throttle']
        );
        // relationship with roles model
        $this->hasManyToMany(
            "id",
            static::$roleUsersModel,
            "user_id",
            "role_id",
            static::$rolesModel,
            "id",
            ['alias'=>'roles']
        );
        // relationship with role_users model
        $this->hasMany(
            'id',
            static::$roleUsersModel,
            'user_id',
            ['alias' => 'roleUsers']
        );
    }    

     /**
     * Returns an array of login column names.
     *
     * @return array
     */
    public function getLoginNames()
    {
        return $this->loginNames;
    }

    /**
     * Get mutator for the "permissions" attribute.
     *
     * @param  mixed  $permissions
     * @return array
     */
    public function getPermissionsAttribute($permissions)
    {
        return $permissions ? json_decode($permissions, true) : [];
    }

    /**
     * Set mutator for the "permissions" attribute.
     *
     * @param  mixed  $permissions
     * @return void
     */
    public function setPermissionsAttribute(array $permissions)
    {
        $this->permissions = $permissions ? json_encode($permissions) : '';
    }

    /**
     * Returns all the associated roles.
     *
     * @return \IteratorAggregate
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if the user is in the given role.
     *
     * @param  mixed  $role
     * @return bool
     */
    public function inRole($role)
    {
        foreach ($this->roles as $instance) {
            if ($role instanceof RoleInterface) {
                return $instance->getRoleId() === $role->getRoleId();
            }

            if ($instance->getRoleId() == $role || $instance->getRoleSlug() == $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a random persist code.
     *
     * @return string
     */
    public function generatePersistenceCode()
    {
        return (new \Phalcon\Security\Random)->base64Safe();
    }

    /**
     * Returns the user primary key.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getKey();
    }

    /**
     * Returns the persistable key value.
     *
     * @return string
     */
    public function getPersistableId()
    {
        return $this->getKey();
    }

    /**
     * Returns the persistable key name.
     *
     * @return string
     */
    public function getPersistableKey()
    {
        return $this->persistableKey;
    }

    /**
     * sets the persistable key name.
     *
     * @return string
     */
    public function setPersistableKey($key)
    {
        $this->persistableKey = $key;
    }

    /**
     * Returns the persistable relationship name.
     *
     * @return string
     */
    public function getPersistableRelationship()
    {
        return $this->persistableRelationship;
    }

    /**
     * Returns the user login.
     *
     * @return string
     */
    public function getUserLogin()
    {
        return $this->{$this->getUserLoginName()};
    }

    /**
     * Returns the user login attribute name.
     *
     * @return string
     */
    public function getUserLoginName()
    {
        return reset($this->loginNames);
    }

    /**
     * Returns the user password.
     *
     * @return string
     */
    public function getUserPassword()
    {
        return $this->password;
    }

    /**
     * Returns the roles model.
     *
     * @return string
     */
    public static function getRolesModel()
    {
        return static::$rolesModel;
    }

    /**
     * Sets the roles model.
     *
     * @param  string  $rolesModel
     * @return void
     */
    public static function setRolesModel($rolesModel)
    {
        static::$rolesModel = $rolesModel;
    }

    /**
     * Returns the persistences model.
     *
     * @return string
     */
    public static function getPersistencesModel()
    {
        return static::$persistencesModel;
    }

    /**
     * Sets the persistences model.
     *
     * @param  string  $persistencesModel
     * @return void
     */
    public static function setPersistencesModel($persistencesModel)
    {
        static::$persistencesModel = $persistencesModel;
    }

    /**
     * Returns the activations model.
     *
     * @return string
     */
    public static function getActivationsModel()
    {
        return static::$activationsModel;
    }

    /**
     * Sets the activations model.
     *
     * @param  string  $activationsModel
     * @return void
     */
    public static function setActivationsModel($activationsModel)
    {
        static::$activationsModel = $activationsModel;
    }

    /**
     * Returns the reminders model.
     *
     * @return string
     */
    public static function getRemindersModel()
    {
        return static::$remindersModel;
    }

    /**
     * Sets the reminders model.
     *
     * @param  string  $remindersModel
     * @return void
     */
    public static function setRemindersModel($remindersModel)
    {
        static::$remindersModel = $remindersModel;
    }

    /**
     * Returns the throttling model.
     *
     * @return string
     */
    public static function getThrottlingModel()
    {
        return static::$throttlingModel;
    }

    /**
     * Sets the throttling model.
     *
     * @param  string  $throttlingModel
     * @return void
     */
    public static function setThrottlingModel($throttlingModel)
    {
        static::$throttlingModel = $throttlingModel;
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
     * Delete related records before deleting a user
     */
    public function beforeDelete()
    {
        $this->activations->delete();
        $this->persistences->delete();
        $this->reminders->delete();
        $this->throttle->delete();
        $this->roleUsers->delete();
    }

    /**
     * Dynamically pass missing methods to the user.
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
     * Creates a permissions object.
     *
     * @return \Cartalyst\Sentinel\Permissions\PermissionsInterface
     */
    protected function createPermissions()
    {
        $userPermissions = $this->permissions;

        $rolePermissions = [];

        foreach ($this->roles as $role) {
            $rolePermissions[] = $role->permissions;
        }

        return new static::$permissionsClass($userPermissions, $rolePermissions);
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
