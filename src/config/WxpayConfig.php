<?php

namespace Apay\config;;

class WxPayConfig
{
	const APPID = 'wx9154c943f60fb786';
	const MCHID = '1488336752';
	const KEY = 'd8997507ef794cf7b34e7d150519453e';
	const APPSECRET = '1f65c57e15409d9b89b01ad118eb502f';
	
	const SSLCERT_PATH = '../cert/apiclient_cert.pem';
	const SSLKEY_PATH = '../cert/apiclient_key.pem';
	
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	
	const REPORT_LEVENL = 1;
}
