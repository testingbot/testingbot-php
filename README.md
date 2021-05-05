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

### getDevices
Gets a list of devices you can test on

```php
$api->getDevices();
```

### getAvailableDevices
Gets a list of available devices you can test on

```php
$api->getAvailableDevices();
```

### getDevice
Gets information for a specific device

```php
$api->getDevice($deviceID);
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

### deleteBuild
Deletes a build from TestingBot.

```php
$api->deleteBuild($buildIdentifier);
```

### getTunnels
Gets a list of active tunnels for your account.

```php
$api->getTunnels();
```

### deleteTunnel
Deletes an active tunnel.

```php
$api->deleteTunnel($tunnelID);
```

### uploadLocalFileToStorage
Uploads a local file (.apk, .ipa, .zip) to TestingBot Storage.

```php
$api->uploadLocalFileToStorage($pathToLocalFile);
```

### uploadRemoteFileToStorage
Uploads a remote file (.apk, .ipa, .zip) to TestingBot Storage.

```php
$api->uploadRemoteFileToStorage($urlToRemoteFile);
```

### getStorageFile
Gets meta data from a file previously uploaded to TestingBot Storage.
AppUrl is the `tb://` url you previously received from the TestingBot API.

```php
$api->getStorageFile($appUrl);
```

### getStorageFiles
Gets meta data from all file previously uploaded to TestingBot Storage.

```php
$api->getStorageFiles();
```

### deleteStorageFile
Deletes a file previously uploaded to TestingBot Storage.
AppUrl is the `tb://` url you previously received from the TestingBot API.

```php
$api->deleteStorageFile($appUrl);
```

### getAuthenticationHash
Calculates the hash necessary to share tests with other people

```php
$api->getAuthenticationHash($identifier);
```