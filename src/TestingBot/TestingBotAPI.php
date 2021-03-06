<?php

namespace TestingBot;

class TestingBotAPI
{
	private $_key;
	private $_secret;
	private $_options = array();

	public function __construct($key, $secret, array $options = array())
	{
		$this->_key = $key;
		$this->_secret = $secret;

		if (empty($key))
		{
			throw new \Exception("Key is required to use the TestingBot API");
		}

		if (empty($secret))
		{
			throw new \Exception("Secret is required to use the TestingBot API");
		}

		$this->_options = $options;
	}

	public function updateJob($sessionID, array $details)
	{
		if (empty($sessionID))
		{
			throw new \Exception("Please use a valid WebDriver sessionID");
		}

		$data = array();

		foreach ($details as $k => $v)
		{
			$data['test[' . $k . ']'] = $v;
		}

		return $this->_doRequest("tests/" . $sessionID, "PUT", $data);
	}

	public function getJob($sessionID, array $details = array())
	{
		if (empty($sessionID))
		{
			throw new \Exception("Please use a valid WebDriver sessionID");
		}

		$extra = "";
		if (!empty($details))
		{
			$extra = "?" . http_build_query($details);
		}
		return $this->_doRequest("tests/" . $sessionID . $extra, "GET");
	}

	public function getJobs($offset = 0, $count = 10, array $extraOptions = array())
	{
		$queryData = array(
			'offset' => $offset,
			'count' => $count
		);

		$queryData = array_merge($queryData, $extraOptions);

		return $this->_doRequest("tests/?" . http_build_query($queryData), "GET");
	}

	public function createLabTest($name, $baseUrl, $extras = array())
	{
		$postData = array_merge(array(
			'test[name]' => $name,
			'test[url]' => $baseUrl
		), $extras);
		return $this->_doRequest("lab/", "POST", $postData);
	}

	public function modifyLabTestSteps($labTestID, array $steps)
	{
		$processedSteps = array();

		for ($i = 0; $i < sizeof($steps); $i++) {
			$processedSteps[] = "steps[][order]=" . $i . "&steps[][cmd]=" . $steps[$i]['cmd'] . "&steps[][locator]=" . $steps[$i]['locator'] . "&steps[][value]=" . $steps[$i]['value'];
		}

		return $this->_doRequest("lab/" . $labTestID . "/steps", "POST", implode('&', $processedSteps));
	}

	public function deleteJob($sessionID)
	{
		if (empty($sessionID))
		{
			throw new \Exception("Please use a valid WebDriver sessionID");
		}

		return $this->_doRequest("tests/" . $sessionID, "DELETE");
	}

	public function stopJob($sessionID)
	{
		if (empty($sessionID))
		{
			throw new \Exception("Please use a valid WebDriver sessionID");
		}

		return $this->_doRequest("tests/" . $sessionID . "/stop", "PUT");
	}

	public function getBuilds($offset = 0, $count = 10)
	{
		$queryData = array(
			'offset' => $offset,
			'count' => $count
		);

		return $this->_doRequest("builds/?" . http_build_query($queryData), "GET");
	}

	public function getBuild($buildID)
	{
		if (empty($buildID))
		{
			throw new \Exception("Please use a valid buildID");
		}

		return $this->_doRequest("builds/" . $buildID, "GET");
	}

	public function deleteBuild($buildID)
	{
		if (empty($buildID))
		{
			throw new \Exception("Please use a valid buildID");
		}

		return $this->_doRequest("builds/" . $buildID, "DELETE");
	}

	public function getTunnels()
	{
		return $this->_doRequest("tunnel/list", "GET");
	}

	public function deleteTunnel($tunnelID)
	{
		return $this->_doRequest("tunnel/" . $tunnelID, "DELETE");
	}

	public function getUserInfo()
	{
		return $this->_doRequest("user", "GET");
	}

	public function getBrowsers()
	{
		return $this->_doRequest("browsers", "GET");
	}

	public function getDevices()
	{
		return $this->_doRequest("devices", "GET");
	}

	public function getAvailableDevices()
	{
		return $this->_doRequest("devices/available", "GET");
	}

	public function getDevice($deviceID)
	{
		return $this->_doRequest("devices/" . $deviceID, "GET");
	}

	public function updateUserInfo(array $details)
	{
		$data = array();

		foreach ($details as $k => $v)
		{
			$data['user[' . $k . ']'] = $v;
		}

		return $this->_doRequest("user", "PUT", $data);
	}

	public function getAuthenticationHash($identifier = null) {
		if (!is_null($identifier)) 
		{
			return md5($this->_key . ":" . $this->_secret . ":" . $identifier);
		}

		return md5($this->_key . ":" . $this->_secret);
	}

	public function uploadLocalFileToStorage($localFile)
	{
		if (!file_exists($localFile))
		{
			throw new \Exception("Could not find file to upload: " . $localFile);
		}
		$mime = mime_content_type($localFile);
		$fileInfo = pathinfo($localFile);
		$fileName = $fileInfo['basename'];
		$cfile = curl_file_create($localFile, $mime, $fileName);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://api.testingbot.com/v1/storage");
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, $this->_key . ":" . $this->_secret);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
			'file' => $cfile
		));

		$response = curl_exec($curl);
		curl_close($curl);

		return $this->_parseResult($response);
	}

	public function uploadRemoteFileToStorage($remoteFileUrl)
	{
		return $this->_doRequest("storage", "POST", array(
			'url' => $remoteFileUrl
		));
	}

	public function getStorageFile($appUrl)
	{
		return $this->_doRequest("storage/" . str_replace("tb://", "", $appUrl), "GET");
	}

	public function getStorageFiles($offset = 0, $count = 10)
	{
		$queryData = array(
			'offset' => $offset,
			'count' => $count
		);

		return $this->_doRequest("storage/?" . http_build_query($queryData), "GET");
	}

	public function deleteStorageFile($appUrl)
	{
		return $this->_doRequest("storage/" . str_replace("tb://", "", $appUrl), "DELETE");
	}

	private function _doRequest($path, $method = 'POST', $data = array())
	{
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.testingbot.com/v1/" . $path);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_key . ":" . $this->_secret);
        if (!empty($data))
        {
        	if (is_array($data))
        	{
        		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        	}
        	else
        	{
        		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        	}
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $this->_parseResult($response);
	}

	private function _parseResult($response)
	{
		$result = json_decode($response, true);
		return $result;
	}
}
