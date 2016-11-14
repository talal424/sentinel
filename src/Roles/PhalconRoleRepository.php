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

use Cartalyst\Support\Traits\RepositoryTrait;

class PhalconRoleRepository implements RoleRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Phalcon role model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Roles\PhalconRole';

    /**
     * Create a new Phalcon role repository.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct($model = null)
    {
        if (isset($model)) {
            $this->model = $model;
        }
    }

    /**
     * Finds a role by the given primary key.
     *
     * @param  int  $id
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findById($id)
    {
        return $this
            ->createModel()
            ->findFirst($id);
    }

    /**
     * Finds a role by the given slug.
     *
     * @param  string  $slug
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findBySlug($slug)
    {
        return $this
            ->createModel()
            ->findFirstBySlug($slug);
    }

    /**
     * Finds a role by the given name.
     *
     * @param  string  $name
     * @return \Cartalyst\Sentinel\Roles\RoleInterface
     */
    public function findByName($name)
    {
        return $this
            ->createModel()
            ->findFirstByName($name);
    }
}
