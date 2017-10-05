[![Build Status](https://travis-ci.org/testingbot/testingbot-php.svg?branch=master)](https://travis-ci.org/testingbot/testingbot-php)

Testingbot-PHP
=======

This is the TestingBot PHP client which makes it easy to 
interact with the [TestingBot API](https://testingbot.com/support/api)

License
-------
Testingbot-PHP is available under the Apache 2 license. See `LICENSE.APACHE2` for more
details.

Usage
----------

TestingBot-PHP is distributed with Composer, which means you can include it in your project:

`composer require testingbot/testingbot-php`

or edit the `composer.json` file and add:

```json
{
    "require": {
        "testingbot/testingbot-php": ">=1.0.0"
    }
}
```

To start, create a new `TestingBot\TestingBotAPI` object and pass in the key and secret you obtained from [TestingBot](https://testingbot.com/members/user/edit) 

```php
$api = new TestingBot\TestingBotAPI($key, $secret);
```

Now you can use the various methods we've made available to interact with the API:

### getBrowsers
Gets a list of browsers you can test on

```php
$api->getBrowsers();
```


### getUserInfo
Gets your user information

```php
$api->getUserInfo();
```

### updateUserInfo
Updates your user information

```php
$api->updateUserInfo(array('first_name' => 'test'));
```

### updateJob
Updates a Test with Meta-data to display on TestingBot.
For example, you can specify the test name and whether the test succeeded or failed:

```php
$api->updateJob($webdriverSessionID, array('name' => 'mytest', 'success' => true));
```

### getJob
Gets meta information for a job (test) by passing in the WebDriver sessionID of the test you ran on TestingBot:

```php
$api->getJob($webdriverSessionID);
```

### getJobs
Gets a list of previous jobs/tests that you ran on TestingBot, order by last run:

```php
$api->getJobs(0, 10); // last 10 tests
```

### deleteJob
Deletes a test from TestingBot

```php
$api->deleteJob($webdriverSessionID);
```

### stopJob
Stops a running test on TestingBot

```php
$api->stopJob($webdriverSessionID);
```

### getBuilds
Gets a list of builds that you ran on TestingBot, order by last run:

```php
$api->getBuilds(0, 10); // last 10 builds
```

### getBuild
Gets a build from TestingBot (a group of tests)

```php
$api->getBuild($buildIdentifier);
```

### getTunnels
Gets a list of active tunnels for your account.

```php
$api->getTunnels();
```

### getAuthenticationHash
Calculates the hash necessary to share tests with other people

```php
$api->getAuthenticationHash($identifier);
```