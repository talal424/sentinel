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

namespace Cartalyst\Sentinel\Reminders;

use Carbon\Carbon;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Users\UserRepositoryInterface;
use Cartalyst\Support\Traits\RepositoryTrait;

class PhalconReminderRepository implements ReminderRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The user repository.
     *
     * @var \Cartalyst\Sentinel\Users\UserRepositoryInterface
     */
    protected $users;

    /**
     * The Phalcon reminder model name.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Reminders\PhalconReminder';

    /**
     * The expiration time in seconds.
     *
     * @var int
     */
    protected $expires = 259200;

    /**
     * Create a new Phalcon reminder repository.
     *
     * @param  \Cartalyst\Sentinel\Users\UserRepositoryInterface  $users
     * @param  string  $model
     * @param  int  $expires
     * @return void
     */
    public function __construct(UserRepositoryInterface $users, $model = null, $expires = null)
    {
        $this->users = $users;

        if (isset($model)) {
            $this->model = $model;
        }

        if (isset($expires)) {
            $this->expires = $expires;
        }
    }

    /**
     * Create a new reminder record and code.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @return \Cartalyst\Sentinel\Users\UserRepositoryInterface
     */
    public function create(UserInterface $user)
    {
        $code = $this->generateReminderCode();

        $reminder = $this->createModel();
        $reminder->code = (string) $code;
        $reminder->completed = (int) false;
        $reminder->user_id = (int) $user->getUserId();
        $reminder->save();

        return $reminder;
    }

    /**
     * Check if a valid reminder exists.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  string  $code
     * @return bool
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

        $reminder = $this
            ->createModel()
            ->findFirst([
                'conditions' => $conditions,
                'bind' => $binds
            ]);

        return $reminder ?: false;
    }

    /**
     * Complete reminder for the given user.
     *
     * @param  \Cartalyst\Sentinel\Users\UserInterface  $user
     * @param  string  $code
     * @param  string  $password
     * @return bool
     */
    public function complete(UserInterface $user, $code, $password)
    {
        $expires = $this->expires();

        $reminder = $this
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

        if (!$reminder) {
            return false;
        }

        $credentials = compact('password');

        $valid = $this->users->validForUpdate($user, $credentials);

        if ($valid === false) {
            return false;
        }

        $this->users->update($user, $credentials);

        $reminder->completed = (int) true;
        $reminder->completed_at = (string) Carbon::now();
        $reminder->save();
        
        return true;
    }

    /**
     * Remove expired reminder codes.
     *
     * @return int
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
     * Returns a random string for a reminder code.
     *
     * @return string
     */
    protected function generateReminderCode()
    {
        return (new \Phalcon\Security\Random)->base64Safe();
    }
}
