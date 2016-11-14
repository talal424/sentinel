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

trait PhalconUserTrait
{
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
    public $email;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $password;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $permissions;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $last_login;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $first_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $last_name;

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
     * @return AuthUsers[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return AuthUsers
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
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
}
