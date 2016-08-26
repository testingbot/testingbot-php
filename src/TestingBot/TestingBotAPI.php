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

	public function getTunnels()
	{
		return $this->_doRequest("tunnels/", "GET");
	}

	public function getUserInfo()
	{
		return $this->_doRequest("user", "GET");
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

	private function _doRequest($path, $method = 'POST', array $data = array())
	{
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.testingbot.com/v1/" . $path);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_key . ":" . $this->_secret);
        if (!empty($data))
        {
        	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $this->_parseResult($response);
	}

	private function _parseResult($response)
	{
		$result = json_decode($response, true);
		if (empty($result))
		{
			throw new \Exception("An API error occurred: " . print_r($response, true));
		}

		return $result;
	}
}
