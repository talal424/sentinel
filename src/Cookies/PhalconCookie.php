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

namespace Cartalyst\Sentinel\Cookies;
use LogicException;
use \Phalcon\Mvc\User\Component;

class PhalconCookie extends Component implements CookieInterface
{
    /**
     * The cookie options.
     *
     * @var array
     */
    protected $options = [
        'name'      => 'cartalyst_sentinel',
        'domain'    => '',
        'path'      => '/',
        'secure'    => false,
        'http_only' => false,
    ];

    /**
     * Phalcon Cookies Service Name.
     *
     * @var string
     */
    protected $service = 'cookies';

    /**
     * Create a new cookie driver.
     *
     * @param  string|array  $options
     * @return void
     */
    public function __construct($options = [], $PhalconCookiesService = null)
    {
        if (isset($PhalconCookiesService)) {
            $this->service = $PhalconCookiesService;
        }

        $di = $this->getDi();

        if (!$di->has($this->service)) {
            throw new LogicException('Cookies service is not injected');
        }

        $this->cookies = $di->get($this->service);

        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options['name'] = $options;
        }
    }

    /**
     * Put a value in the Sentinel cookie (to be stored until it's cleared).
     *
     * @param  mixed  $value
     * @return void
     */
    public function put($value)
    {
        $this->setCookie($value, $this->minutesToLifetime(2628000));
    }

    /**
     * Returns the Sentinel cookie value.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->getCookie();
    }

    /**
     * Remove the Sentinel cookie.
     *
     * @return void
     */
    public function forget()
    {
        $this->put(null, -2628000);
    }

    /**
     * Takes a minutes parameter (relative to now)
     * and converts it to a lifetime (unix timestamp).
     *
     * @param  int  $minutes
     * @return int
     */
    protected function minutesToLifetime($minutes)
    {
        return time() + ($minutes * 60);
    }

    /**
     * Returns a PHP cookie.
     *
     * @return mixed
     */
    protected function getCookie()
    {
        if ($this->cookies->has($this->options['name'])) {
            $value = $this->cookies->get($this->options['name']);

            if ($value) {
                return json_decode($value);
            }
        }
    }

    /**
     * Sets a PHP cookie.
     *
     * @param  mixed  $value
     * @param  int  $lifetime
     * @param  string  $path
     * @param  string  $domain
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @return void
     */
    protected function setCookie($value, $lifetime, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        $this->cookies->set(
            $this->options['name'],
            json_encode($value),
            $lifetime,
            $path ?: $this->options['path'],
            $secure ?: $this->options['secure'],
            $domain ?: $this->options['domain'],
            $httpOnly ?: $this->options['http_only']
        );
    }
}
