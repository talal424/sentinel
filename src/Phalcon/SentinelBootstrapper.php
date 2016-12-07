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

namespace Cartalyst\Sentinel\Phalcon;

use Cartalyst\Sentinel\Activations\PhalconActivationRepository;
use Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint;
use Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint;
use Cartalyst\Sentinel\Cookies\PhalconCookie;
use Cartalyst\Sentinel\Hashing\PhalconHasher;
use Cartalyst\Sentinel\Persistences\PhalconPersistenceRepository;
use Cartalyst\Sentinel\Reminders\PhalconReminderRepository;
use Cartalyst\Sentinel\Roles\PhalconRoleRepository;
use Cartalyst\Sentinel\PhalconSentinel;
use Cartalyst\Sentinel\Sessions\PhalconSession;
use Cartalyst\Sentinel\Throttling\PhalconThrottleRepository;
use Cartalyst\Sentinel\Users\PhalconUserRepository;
use \Phalcon\Events\Manager as Dispatcher;
use InvalidArgumentException;
use \Phalcon\Http\Request;

class SentinelBootstrapper
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The Phalcon Events Manager
     *
     * @var \Phalcon\Events\Manager
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct($config = null)
    {
        if (is_string($config)) {
            $this->config = new ConfigRepository($config);
        } else {
            $this->config = $config ?: new ConfigRepository;
        }
    }

    /**
     * Creates a sentinel instance.
     *
     * @return \Cartalyst\Sentinel\Sentinel
     */
    public function createSentinel()
    {
        $persistence = $this->createPersistence();
        $users       = $this->createUsers();
        $roles       = $this->createRoles();
        $activations = $this->createActivations();
        $dispatcher  = $this->getEventDispatcher();

        $sentinel = new PhalconSentinel(
            $persistence,
            $users,
            $roles,
            $activations,
            $dispatcher
        );

        $throttle = $this->createThrottling();

        $ipAddress = $this->getIpAddress();

        $checkpoints = $this->createCheckpoints($activations, $throttle, $ipAddress);

        foreach ($checkpoints as $key => $checkpoint) {
            $sentinel->addCheckpoint($key, $checkpoint);
        }

        $reminders = $this->createReminders($users);

        $sentinel->setActivationRepository($activations);

        $sentinel->setReminderRepository($reminders);

        $sentinel->setThrottleRepository($throttle);

        return $sentinel;
    }

    /**
     * Creates a persistences repository.
     *
     * @return \Cartalyst\Sentinel\Persistences\PhalconPersistenceRepository
     */
    protected function createPersistence()
    {
        $session = $this->createSession();

        $cookie = $this->createCookie();

        $model = $this->config['persistences']['model'];

        $single = $this->config['persistences']['single'];

        return new PhalconPersistenceRepository($session, $cookie, $model, $single);
    }

    /**
     * Creates a session.
     *
     * @return \Cartalyst\Sentinel\Sessions\PhalconSession
     */
    protected function createSession()
    {
        $service = $this->config['PhalconServices']['SessionService'];
        return new PhalconSession($this->config['session'], $service);
    }

    /**
     * Creates a cookie.
     *
     * @return \Cartalyst\Sentinel\Cookies\PhalconCookie
     */
    protected function createCookie()
    {
        $service = $this->config['PhalconServices']['CookiesService'];
        return new PhalconCookie($this->config['cookie'], $service);
    }

    /**
     * Creates a user repository.
     *
     * @return \Cartalyst\Sentinel\Users\PhalconUserRepository
     */
    protected function createUsers()
    {
        $hasher = $this->createHasher();

        $model = $this->config['users']['model'];

        $roles = $this->config['roles']['model'];

        $roleUsers = $this->config['roleUsers']['model'];

        $persistences = $this->config['persistences']['model'];

        if (class_exists($roles)) {
            if (method_exists($roles, 'setUsersModel')) {
                forward_static_call_array([$roles, 'setUsersModel'], [$model]);
            }
            if (method_exists($roles, 'setRoleUsersModel')) {
                forward_static_call_array([$roles, 'setRoleUsersModel'], [$roleUsers]);
            }
        }

        if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
            forward_static_call_array([$persistences, 'setUsersModel'], [$model]);
        }

        return new PhalconUserRepository($hasher, $this->getEventDispatcher(), $model);
    }

    /**
     * Creates a hasher.
     *
     * @return \Cartalyst\Sentinel\Hashing\PhalconHasher
     */
    protected function createHasher()
    {
        $service = $this->config['PhalconServices']['SecurityService'];
        return new PhalconHasher($service);
    }

    /**
     * Creates a role repository.
     *
     * @return \Cartalyst\Sentinel\Roles\PhalconRoleRepository
     */
    protected function createRoles()
    {
        $model = $this->config['roles']['model'];

        $users = $this->config['users']['model'];

        $roleUsers = $this->config['roleUsers']['model'];

        if (class_exists($users)) {
            if (method_exists($users, 'setRolesModel')) {
                forward_static_call_array([$users, 'setRolesModel'], [$model]);
            }
            if (method_exists($users, 'setRoleUsersModel')) {
                forward_static_call_array([$users, 'setRoleUsersModel'], [$roleUsers]);
            }
        }

        return new PhalconRoleRepository($model);
    }

    /**
     * Creates an activation repository.
     *
     * @return \Cartalyst\Sentinel\Activations\PhalconActivationRepository
     */
    protected function createActivations()
    {
        $model = $this->config['activations']['model'];

        $expires = $this->config['activations']['expires'];

        return new PhalconActivationRepository($model, $expires);
    }

    /**
     * Returns the client's ip address.
     *
     * @return string
     */
    protected function getIpAddress()
    {

        return (new Request)->getClientAddress();
    }

    /**
     * Create an activation checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Activations\PhalconActivationRepository  $activations
     * @return \Cartalyst\Sentinel\Checkpoints\ActivationCheckpoint
     */
    protected function createActivationCheckpoint(PhalconActivationRepository $activations)
    {
        return new ActivationCheckpoint($activations);
    }

    /**
     * Create activation and throttling checkpoints.
     *
     * @param  \Cartalyst\Sentinel\Activations\PhalconActivationRepository  $activations
     * @param  \Cartalyst\Sentinel\Throttling\PhalconThrottleRepository  $throttle
     * @param  string  $ipAddress
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function createCheckpoints(PhalconActivationRepository $activations, PhalconThrottleRepository $throttle, $ipAddress)
    {
        $activeCheckpoints = $this->config['checkpoints'];

        $activation = $this->createActivationCheckpoint($activations);

        $throttle = $this->createThrottleCheckpoint($throttle, $ipAddress);

        $checkpoints = [];

        foreach ($activeCheckpoints as $checkpoint) {
            if (! isset($$checkpoint)) {
                throw new InvalidArgumentException("Invalid checkpoint [{$checkpoint}] given.");
            }

            $checkpoints[$checkpoint] = $$checkpoint;
        }

        return $checkpoints;
    }

    /**
     * Create a throttle checkpoint.
     *
     * @param  \Cartalyst\Sentinel\Throttling\PhalconThrottleRepository  $throttle
     * @param  string  $ipAddress
     * @return \Cartalyst\Sentinel\Checkpoints\ThrottleCheckpoint
     */
    protected function createThrottleCheckpoint(PhalconThrottleRepository $throtte, $ipAddress)
    {
        return new ThrottleCheckpoint($throtte, $ipAddress);
    }

    /**
     * Create a throttling repository.
     *
     * @return \Cartalyst\Sentinel\Throttling\PhalconThrottleRepository
     */
    protected function createThrottling()
    {
        $model = $this->config['throttling']['model'];

        foreach (['global', 'ip', 'user'] as $type) {
            ${"{$type}Interval"} = $this->config['throttling'][$type]['interval'];

            ${"{$type}Thresholds"} = $this->config['throttling'][$type]['thresholds'];
        }

        return new PhalconThrottleRepository(
            $model,
            $globalInterval,
            $globalThresholds,
            $ipInterval,
            $ipThresholds,
            $userInterval,
            $userThresholds
        );
    }

    /**
     * Returns the Phalcon Events Manager
     *
     * @return \Phalcon\Events\Manager
     */
    protected function getEventDispatcher()
    {
        if (! $this->dispatcher) {
            $this->dispatcher = new Dispatcher;
        }

        return $this->dispatcher;
    }

    /**
     * Create a reminder repository.
     *
     * @param  \Cartalyst\Sentinel\Users\PhalconUserRepository  $users
     * @return \Cartalyst\Sentinel\Reminders\PhalconReminderRepository
     */
    protected function createReminders(PhalconUserRepository $users)
    {
        $model = $this->config['reminders']['model'];

        $expires = $this->config['reminders']['expires'];

        return new PhalconReminderRepository($users, $model, $expires);
    }
}
