<?php
	
	if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
	    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
	} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
	    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
	}

	require_once('src/TestingBot/TestingBotAPI.php');

	class TestingBotTest extends PHPUnit_Framework_TestCase {
		private $api;

		protected function setUp(): void {
			$key = getenv("TB_KEY");
			$secret = getenv("TB_SECRET");

			$this->api = new TestingBot\TestingBotAPI($key, $secret);
		}

		public function testGetSingleTest() {
			$sessionID = "6344353dcee24694bf39d5ee5e6e5b11";
	        $test = $this->api->getJob($sessionID);
	        $this->assertEquals($test['session_id'], $sessionID);
		}

		public function testGetUnknownTest() {
			$sessionID = "unknown";
	        $test = $this->api->getJob($sessionID);
	        $this->assertArrayHasKey('error', $test);
		}

		public function testDeleteUnknownTest() {
        	$sessionID = "unknown";
	        $test = $this->api->deleteJob($sessionID);
	        $this->assertArrayHasKey('error', $test);
    	}

    	public function testGetTests() {
	        $tests = $this->api->getJobs(0, 10);
	        $this->assertCount(10, $tests['data']);
    	}

    	public function testGetUser() {
	        $user = $this->api->getUserInfo();
	        $this->assertEquals("bot", $user['last_name']);
    	}

    	public function testUnauthorizedCall() {
    		try {
	    		$api = new TestingBot\TestingBotAPI('', '');
		        $user = $api->getUserInfo();
		        $this->assertEquals(true, false);
    		} catch (Exception $e) {
    			$this->assertEquals(true, true);
    		}
    	}

    	public function testGetTunnels() {
	        $tunnels = $this->api->getTunnels();
	        $this->assertCount(0, $tunnels);
    	}

    	public function testGetBrowsers() {
	        $browsers = $this->api->getBrowsers();
	        $this->assertEquals(sizeof($browsers) > 0, true);
    	}

    	public function testCalculateAuthentication() {
    		$this->assertEquals($this->api->getAuthenticationHash("test"), "344ebf07233168c4882adf953a8a8c9b");
    	}

    	public function testUploadLocalFileToStorage() {
    		$response = $this->api->uploadLocalFileToStorage(realpath(dirname(__FILE__) . '/resources/test.apk'));
    		$this->assertStringContainsStringIgnoringCase('tb://', $response['app_url']);
    	}

    	public function testUploadRemoteFileToStorage() {
    		$response = $this->api->uploadRemoteFileToStorage('https://testingbot.com/appium/sample.apk');
    		$this->assertStringContainsStringIgnoringCase('tb://', $response['app_url']);
    	}

    	public function testGetStoredFiles() {
    		$response = $this->api->getStorageFiles();
    		$currentTotal = $response['meta']['total'];
    		$this->api->uploadLocalFileToStorage(realpath(dirname(__FILE__) . '/resources/test.apk'));
    		$response = $this->api->getStorageFiles();
    		$this->assertEquals($response['meta']['total'], $currentTotal + 1);
    	}

    	public function testStoredFileMetaData() {
    		$response = $this->api->uploadLocalFileToStorage(realpath(dirname(__FILE__) . '/resources/test.apk'));
    		$meta = $this->api->getStorageFile($response['app_url']);
    		$this->assertEquals($meta['app_url'], $response['app_url']);
    	}

    	public function testDeleteStorageFile() {
    		$response = $this->api->uploadLocalFileToStorage(realpath(dirname(__FILE__) . '/resources/test.apk'));
    		$meta = $this->api->getStorageFile($response['app_url']);
    		$this->assertEquals($meta['app_url'], $response['app_url']);
    		$this->api->deleteStorageFile($response['app_url']);
    		$meta = $this->api->getStorageFile($response['app_url']);
    		$this->assertArrayHasKey('error', $meta);
    	}
	}