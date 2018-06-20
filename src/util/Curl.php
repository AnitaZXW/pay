<?php 

namespace Apay\util\Curl;

class Curl
{
	
	public static function curlPost($url, $postData)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		$response = curl_exec($curl);
		if ($response) {
			curl_close($ch);
			return $response;
		} else {
			$error = curl_error($ch);
			//将error写入log
			curl_close($ch);
			return false;
		}
	}

	public static function curlGet()
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		$response = curl_exec($curl);
		if ($response) {
			curl_close($ch);
			return $response;
		} else {
			$error = curl_error($ch);
			//将error写入log
			curl_close($ch);
			return false;
		}
	}

	public static function getClientIp()
	{
		$s_client_ip = '';
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$s_client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_X_REAL_IP']))
		{
			$s_client_ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		elseif ($_SERVER['REMOTE_ADDR'])
		{
			$s_client_ip = $_SERVER['REMOTE_ADDR'];
		}
		elseif (getenv('REMOTE_ADDR'))
		{
			$s_client_ip = getenv('REMOTE_ADDR');
		}
		elseif (getenv('HTTP_CLIENT_IP'))
		{
			$s_client_ip = getenv('HTTP_CLIENT_IP');
		}
		else
		{
			$s_client_ip = 'unknown';
		}
		
		return $s_client_ip;
	}

}