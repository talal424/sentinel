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

namespace Cartalyst\Sentinel\Activations;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class PhalconActivationRepository implements ActivationRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Phalcon activation model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Activations\PhalconActivation';

    /**
     * The activation expiration time, in seconds.
     *
     * @var int
     */
    protected $expires = 259200;

    /**
     * Create a new Phalcon activation repository.
     *
     * @param  string  $model
     * @param  int  $expires
     * @return void
     */
    public function __construct($model = null, $expires = null)
    {
        if (isset($model)) {
            $this->model = $model;
        }

        if (isset($expires)) {
            $this->expires = $expires;
        }
    }

    /**
     * Create a new activation record and code.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface
     */
    public function create(UserInterface $user)
    {
        $activation = $this->createModel();

        $code = $this->generateActivationCode();

        $activation->code = (string) $code;

        $activation->user_id = (int) $user->getUserId();

        $activation->save();

        return $activation;
    }

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  string  $code
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface|bool
     */
    public function exists(UserInterface $user, $code = null)
    {
        $expires = $this->expires();

        $conditions = [
            ['user_id = ?0'],
            ['completed = ?1'],
            ['created_at > ?2']
        ];

        $binds = [
            (int) $user->getUserId(),
            (int) false,
            (string) $expires
        ];
        
        if ($code) {
            $conditions[] = ['code = ?3'];
            $binds[] = (string) $code;
        }

        $activation = $this
            ->createModel()
            ->findFirst([
                'conditions' => $conditions,
                'bind' => $binds
            ]);

        return $activation ?: false;
    }

    /**
     * Completes the activation for the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  string  $code
     * @return bool
     */
    public function complete(UserInterface $user, $code)
    {
        $expires = $this->expires();

        $activation = $this
            ->createModel()
            ->findFirst([
                'conditions' => [
                    ['user_id = ?0'],
                    ['code = ?1'],
                    ['completed = ?2'],
                    ['created_at > ?3']
                ],
                'bind' => [
                    (int) $user->getUserId(),
                    (string) $code,
                    (int) false,
                    (string) $expires
                ]
            ]);

        if (!$activation) {
            return false;
        }
        $activation->completed = (int) true;
        $activation->completed_at = (string) Carbon::now();

        $activation->save();

        return true;
    }

    /**
     * Checks if a valid activation has been completed.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Activations\ActivationInterface|bool
     */
    public function completed(UserInterface $user)
    {
        $activation = $this
            ->createModel()
            ->findFirst([
                'conditions' => [
                    ['user_id = ?0'],
                    ['completed = ?1']
                ],
                'bind' => [
                    (int) $user->getUserId(),
                    (int) true
                ]
            ]);

        return $activation ?: false;
    }

     /**
     * Remove an existing activation (deactivate).
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return bool|null
     */
    public function remove(UserInterface $user)
    {
        $activation = $this->completed($user);

        if ($activation === false) {
            return false;
        }

        return $activation->delete();
    }

    /**
     * Remove expired activation codes.
     *
     * @return bool
     */
    public function removeExpired()
    {
        $expires = $this->expires();

        return $this
            ->createModel()
            ->find([
                'conditions' => [
                    ['completed = ?0'],
                    ['created_at < ?1']
                ],
                'bind' => [
                    (int) false,
                    (string) $expires
                ]
            ])
            ->delete();
    }

    /**
     * Returns the expiration date.
     *
     * @return \Carbon\Carbon
     */
    protected function expires()
    {
        return Carbon::now()->subSeconds($this->expires);
    }

    /**
     * Return a random string for an activation code.
     *
     * @return string
     */
    protected function generateActivationCode()
    {
        return (new \Phalcon\Security\Random)->base64Safe();
    }
}
