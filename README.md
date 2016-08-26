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

### updateJob
Updates a Test with Meta-data to display on TestingBot.
For example, you can specify the test name and whether the test succeeded or failed:

```php
	$api->updateJob($webdriverSessionID, array('name' => 'mytest', 'success' => true));
```