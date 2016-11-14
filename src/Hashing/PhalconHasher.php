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

namespace Cartalyst\Sentinel\Hashing;

use RuntimeException;
use LogicException;
use \Phalcon\Mvc\User\Component;

class PhalconHasher extends Component implements HasherInterface
{
    /**
     * Phalcon Security Service Name.
     *
     * @var string
     */
    protected $service = 'security';

    /**
     * Change service name and check if its injected
     *
     * @param  string  $PhalconSecurityService
     * @return void
     */
    public function __construct($PhalconSecurityService = null)
    {
        if (isset($PhalconSecurityService)) {
            $this->service = $PhalconSecurityService;
        }

        $di = $this->getDi();

        if (!$di->has($this->service)) {
            throw new LogicException('Security service is not injected');
        } elseif (!$di->has('crypt')) {
            throw new LogicException('Crypt service is not injected');
        } elseif (is_null($di->get('crypt')->getKey())) {
            throw new LogicException('Encryption key cannot be empty');
        }

        $this->security = $di->get($this->service);
    }
    
    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @return string
     * @throws \RuntimeException
     */
    public function hash($value)
    {
        // The salt is generated using pseudo-random bytes with the PHP’s 
        // function openssl_random_pseudo_bytes so is required to have the openssl extension loaded.
        if (empty($hash = $this->security->hash($value))) {
            throw new RuntimeException('Error hashing value.');
        }

        return $hash;
    }

    /**
     * Checks the string against the hashed value.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @return bool
     */
    public function check($value, $hashedValue)
    {
        // To protect against timing attacks.
        $this->security->hash(rand());
        return $this->security->checkHash($value, $hashedValue);
    }
}
