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
 * @author     Talal Alenizi <talal.alenizi@gmail.com> <@Talal_AlEnizi>
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2016, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Sentinel\Phalcon;

use ArrayAccess;

class ConfigRepository implements ArrayAccess
{
    /**
     * The config file path.
     *
     * @var string
     */
    protected $file;

    /**
     * The config data.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param  string  $file
     * @return void
     */
    public function __construct($file = null)
    {
        $this->file = $file ?: __DIR__.'/../config/PhalconConfig.php';

        $this->load();
    }

    /**
     * Load the configuration file.
     *
     * @return void
     */
    protected function load()
    {
        $this->config = require $this->file;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($key)
    {
        return isset($this->config[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($key)
    {
        return $this->config[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($key)
    {
        unset($this->config[$key]);
    }
}