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

namespace Cartalyst\Sentinel\Sessions;
use \Phalcon\Mvc\User\Component;
use LogicException;

class PhalconSession extends Component implements SessionInterface
{
    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'cartalyst_sentinel';

    /**
     * Phalcon Session Service Name.
     *
     * @var string
     */
    protected $service = 'session';

    /**
     * Creates a new Phalcon session driver for Sentinel.
     *
     * @param  string  $key
     * @param  string  $PhalconSessionService
     * @return void
     */
    public function __construct($key = null, $PhalconSessionService = null)
    {
        if (isset($PhalconSessionService)) {
            $this->service = $PhalconSessionService;
        }

        $di = $this->getDi();

        if (!$di->has($this->service)) {
            throw new LogicException('Session service is not injected');
        }

        $this->session = $di->get($this->service);

        if (isset($key)) {
            $this->key = $key;
        }

        $this->startSession();
    }

    /**
     * Called upon destruction of the native session handler.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->writeSession();
    }

    /**
     * Put a value in the Sentinel session.
     *
     * @param  mixed  $value
     * @return void
     */
    public function put($value)
    {
        $this->setSession($value);
    }

    /**
     * Returns the Sentinel session value.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->getSession();
    }

    /**
     * Removes the Sentinel session.
     *
     * @return void
     */
    public function forget()
    {
        $this->forgetSession();
    }

    /**
     * Starts the session if it does not exist.
     *
     * @return void
     */
    protected function startSession()
    {
        // Check that the session hasn't already been started
        if (!$this->session->isStarted()) {
            // Phalcon Session class will check if headers are sent
            $this->session->start();
        }
    }

    /**
     * Writes the session.
     *
     * @return void
     */
    protected function writeSession()
    {
        session_write_close();
    }

    /**
     * Unserializes a value from the session and returns it.
     *
     * @return mixed.
     */
    protected function getSession()
    {
        if ($this->session->has($this->key)) {
            $value = $this->session->get($this->key);

            if ($value) {
                return unserialize($value);
            }
        }
    }

    /**
     * Interacts with the $_SESSION global to set a property on it.
     * The property is serialized initially.
     *
     * @param  mixed  $value
     * @return void
     */
    protected function setSession($value)
    {
        $this->session->set($this->key,serialize($value));
    }

    /**
     * Forgets the Sentinel session from the global $_SESSION.
     *
     * @return void
     */
    protected function forgetSession()
    {
        if ($this->session->has($this->key)) {
            $this->session->remove($this->key);
        }
    }
}
