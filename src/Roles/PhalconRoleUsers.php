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

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use \Phalcon\Mvc\Model;

class PhalconRoleUsers extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_users';

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $role_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        // set dynamic update on
        $this->useDynamicUpdate(true);
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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->table;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoleUsers[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoleUsers
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
    
    /**
     * Set role id
     * 
     * @param int $key
     * @return \Cartalyst\Sentinel\Roles\PhalconRoleUsers
     */
    public function setRoleId($key)
    {
        $this->role_id = $key;
        return $this;
    }
    
    /**
     * Get role id
     * 
     * @return number
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Assign a user to a role.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return void
     */
    public function attach(UserInterface $user)
    {
        $this->user_id = (int) $user->getUserId();
        $this->created_at = (string) Carbon::now();
        $this->updated_at = (string) Carbon::now();
        $this->save();
    }

    /**
     * Remove a user/all users from a role.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return void
     */
    public function detach(UserInterface $user = null)
    {
        // remove all users from a role
        if ($user === null) {
            $this
            ->findByRoleId($this->getRoleId())
            ->delete();
            return;
        }

        // remove a user from a role
        $roleUsers = $this
        ->findFirst([
            'conditions' => [
                ['user_id = ?0'],
                ['role_id = ?1']
            ],
            'bind' => [
                (int) $user->getUserId(),
                (int) $this->getRoleId()
            ]
        ]);

        // check if there is a role record to remove
        if ($roleUsers) {
            $roleUsers->delete();
        }
    }
    
    
}
