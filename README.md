# Cartalyst/Sentinel Phalcon integration

this is a [forked project](https://github.com/talal424/sentinel) that i made a phalcon intergration to it

# Configuration:

the sentinel config file can be found under config/PhalconConfig.php

#Usage:

it relies on \Phalcon\Mvc\Model for database connection

you don't need to require these packages on composer.json:

* illuminate/database
* symfony/http-foundation
* illuminate/events

but you need this package:

* nesbot/carbon

```php
// Import the necessary classes
use Cartalyst\Sentinel\Phalcon\Facades\Sentinel;

// Include the composer autoload file
require 'vendor/autoload.php';

Sentinel::check();
```

# Tested on:

PHP 5.5
Phalcon 3.0.1
