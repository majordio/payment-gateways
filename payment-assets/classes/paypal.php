<?php
ini_set("max_execution_time","300");
ini_set("max_input_time","600");
ini_set("memory_limit","64M");

class Paypal {
	private $apiuser;
	private $apipwd;
	private $apisign;
	private $returnurl;
	private $cancelurl;
	private $notifyurl;
	private $apiversion = '60.0';
	private $apimode;
	private $paypalheader = '';
	private $method = 0; // 0 - new paypal account, 1 - with paypal account
	private $paytype = 'Sale'; // Sale or Authorization
	protected $_logs;

	public function __construct(){

		$this->apimode = TEST_MODE?'sandbox':'live';
		$this->apiuser = PAYPAL_USERNAME;
		$this->apipwd = PAYPAL_PASSWORD;
		$this->apisign = PAYPAL_SIGNATURE;
		$this->returnurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?status=success";
		$this->cancelurl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?status=cancel";

	}

	public function SetExpressCheckout($params){
		// set request
		$request = '';
		$request .= '&Amt='.urlencode($params['total']);
		$request .= '&PAYMENTACTION='.urlencode($this->paytype);
		$request .= '&ReturnUrl='.urlencode($this->returnurl);
		$request .= '&CANCELURL='.urlencode($this->cancelurl);
		$request .= '&CURRENCYCODE='.urlencode($params['currency']);
		$request .= '&NOSHIPPING=1';
		$request .= ($this->method == 0) ? '&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing' : '&SOLUTIONTYPE=Mark&LANDINGPAGE=Login';
		$request .= '&LOCALECODE='.strtoupper($params['localecode']);
		if ($this->paypalheader)
			$request .= '&HDRIMG='.urlencode($this->paypalheader);
		if(!empty($params['customeremail'])){
			$request .= '&EMAIL='.urlencode($params['customeremail']);
		}
		if($params['recurring']){
			$request .= '&L_BILLINGTYPE0='.urlencode('RecurringPayments');
			$request .= '&L_BILLINGAGREEMENTDESCRIPTION0='.urlencode($params['itemname']);
		}
		$request .= '&L_NAME0='.urlencode($params['itemname']);
		$request .= '&L_AMT0='.urlencode($params['total']);
		$request .= '&L_QTY0='.urlencode($params['itemqty']);
		$request .= '&ITEMAMT='.urlencode($params['total']);
		if(!empty($params['shipname'])){
			$request .= '&SHIPTONAME='.urlencode($params['shipname']);
			$request .= '&SHIPTOSTREET='.urlencode($params['shipstreet']);
			$request .= '&SHIPTOSTREET2='.urlencode($params['shipstreet2']);
			$request .= '&SHIPTOCITY='.urlencode($params['shipcity']);
			$request .= '&SHIPTOSTATE='.($params['shipstate']);
			$request .= '&SHIPTOZIP='.urlencode($params['shipzip']);
			$request .= '&SHIPTOCOUNTRY='.urlencode($params['shipcountry']);
			$request .= '&SHIPTOPHONENUM='.urlencode($params['shipphone']);
			$request .= '&ADDROVERRIDE=1';
		}
		// call to paypal
		$result = $this->calltopaypal($this->apiurl(), $this->apiscript(), 'SetExpressCheckout', $request);
		$this->_logs = $this->getLogs();
		return $result;
	}

	public function GetExpressCheckoutDetails($token){
		$request = '&TOKEN='.urlencode($token);
		$result = $this->calltopaypal($this->apiurl(), $this->apiscript(), 'GetExpressCheckoutDetails', $request);
		$this->_logs = $this->getLogs();
		return $result;
	}

	public function DoExpressCheckoutPayment($params, $token, $payerid){
		// set request
		$server = urlencode($_SERVER['SERVER_NAME']);
		$request = '';
		$request .= '&TOKEN='.urlencode($token);
		$request .= '&PAYERID='.urlencode($payerid);
		$request .= '&PAYMENTACTION='.$this->paytype;
		$request .= '&AMT='.$params['total'];
		$request .= '&CURRENCYCODE='.$params['currency'];
		$request .= '&IPADDRESS='.$server;
		$request .= '&NOTIFYURL='.$this->notifyurl;
		$request .= '&L_NAME0='.urlencode($params['itemname']);
		$request .= '&L_AMT0='.urlencode($params['itemamt']);
		$request .= '&L_QTY0='.urlencode($params['itemqty']);
		$request .= '&ITEMAMT='.urlencode($params['total']);
		if(!empty($params['shipname'])){
			$request .= '&SHIPTONAME='.urlencode($params['shipname']);
			$request .= '&SHIPTOSTREET='.urlencode($params['shipstreet']);
			$request .= '&SHIPTOCITY='.urlencode($params['shipcity']);
			$request .= '&SHIPTOSTATE='.urlencode($params['shipstate']);
			$request .= '&SHIPTOCOUNTRYCODE='.urlencode($params['shipcountry']);
			$request .= '&SHIPTOZIP='.urlencode($params['shipzip']);
		}

		// call to paypal
		$result = $this->calltopaypal($this->apiurl(), $this->apiscript(), 'DoExpressCheckoutPayment', $request);

		// Checking PayPal result
		if (!is_array($result) OR !sizeof($result))
			$this->error('Authorization to PayPal failed.', $this->_logs);
		elseif (!isset($result['ACK']) OR  strtoupper($result['ACK']) != 'SUCCESS')
			$this->error('PayPal return error.', $result);
		elseif (!isset($result['TOKEN']) OR $result['TOKEN'] != $this->_logs)
		{
			$this->error('PayPal return error. Token given by PayPal is not the same as the cookie token.');
		}

		unset($_SESSION['token']);
		return $result;
	}

	public function DoDirectPayment($params){
		// set request
		$request = '';
		$request  = '&PAYMENTACTION='.$this->paytype;
		$request .= '&AMT='.urlencode(number_format($params['total'], 2, '.', ''));
		$request .= '&CREDITCARDTYPE='.urlencode($params['cctype']);
		$request .= '&ACCT='.urlencode($params['ccnumber']);
		$request .= '&EXPDATE='.urlencode(str_pad($params['exmonth'], 2, '0', STR_PAD_LEFT)).urlencode($params['exyear']);
		$request .= '&CVV2='.urlencode($params['cvv']);
		$request .= '&FIRSTNAME='.urlencode($params['fnameoncard']);
		$request .= '&LASTNAME='.urlencode($params['lnameoncard']);
		$request .= '&STREET='.urlencode($params['street']);
		$request .= '&CITY='.urlencode($params['city']);
		$request .= '&STATE='.urlencode($params['state']);
		$request .= '&ZIP='.urlencode($params['zip']);
		$request .= '&COUNTRYCODE='.urlencode($params['country']);
		$request .= '&CURRENCYCODE='.urlencode($params['currency']);

		// call to paypal
		$result = $this->calltopaypal($this->apiurl(), $this->apiscript(), 'DoDirectPayment', $request);

		// Checking PayPal result
		if (!is_array($result) OR !sizeof($result))
			$this->error('Authorization to PayPal failed.', $this->_logs);
		elseif (!isset($result['ACK']) OR  strtoupper($result['ACK']) != 'SUCCESS')
			$this->error('PayPal return error.', $result);

		return $result;
	}

	public function CreateRecurringPaymentsProfile($params,$token){
		$request="&TOKEN=".urlencode($token);
		$request.="&PROFILESTARTDATE=".gmdate("Y-m-d\TH:i:s\Z",strtotime('+1 '.$params['recurring_freq']));
		$request.="&DESC=".urlencode($params['itemname']);
		$request.="&BILLINGPERIOD=".urlencode($params['recurring_freq']);
		$request.="&BILLINGFREQUENCY=1";
		$request.="&AMT=".urlencode($params['total']);
		$request.="&CURRENCYCODE=".urlencode($params['currency']);

		$result = $this->calltopaypal($this->apiurl(), $this->apiscript(), 'CreateRecurringPaymentsProfile', $request);

		// Checking PayPal result
		if (!is_array($result) OR !sizeof($result))
			$this->error('Authorization to PayPal failed.', $this->_logs);
		elseif (!isset($result['ACK']) OR  strtoupper($result['ACK']) != 'SUCCESS')
			$this->error('PayPal return error.', $result);

		return $result;
	}

	private function calltopaypal($host, $script, $method, $params){
		// set request
		$request 	 = 'METHOD='.urlencode($method).'&VERSION='.urlencode($this->apiversion);
		$request 	.= '&PWD='.urlencode($this->apipwd).'&USER='.urlencode($this->apiuser);
		$request 	.= '&SIGNATURE='.urlencode($this->apisign).$params;

		// create connection
		$result = $this->createConnection($host, $script, $request, true);
		// get the logs
		$this->_logs = array();
		// get response value
		$response = explode('&', $result);
		foreach ($response as $k => $res)
		{
			$tmp = explode('=', $res);
			if (!isset($tmp[1]))
				$response[$tmp[0]] = urldecode($tmp[0]);
			else
			{
				$response[$tmp[0]] = urldecode($tmp[1]);
				unset($response[$k]);
			}
		}

		$toExclude = array('TOKEN', 'SUCCESSPAGEREDIRECTREQUESTED', 'VERSION', 'BUILD', 'ACK', 'CORRELATIONID');
		$this->_logs[] = '<b>PayPal response:</b>';
		foreach ($response as $k => $res)
		{
			if (in_array($k, $toExclude))
				continue;
			$this->_logs[] = $k.' -> '.$res;
		}

		return $response;
	}

	public function simplecalltopaypal($host, $script, $request)
	{
		$result = $this->makeConnection($host, $script, $request);
		$this->_logs = $this->getLogs();
		return $result;
	}

	public function error($msg, $error = array()){
		foreach ($error AS $key => $string)
			if ($string == 'ACK -> Success')
				$send = false;
			elseif (substr($string, 0, 6) == 'METHOD')
			{
				$values = explode('&', $string);
				foreach ($values AS $key2 => $value)
				{
					$values2 = explode('=', $value);
					foreach ($values2 AS $key3 => $value2)
						if ($value2 == 'PWD' || $value2 == 'SIGNATURE')
							$values2[$key3 + 1] = '*********';
					$values[$key2] = implode('=', $values2);
				}
				$error[$key] = implode('&', $values);
			}
		if(!empty($error)){
			$_SESSION['paypal_errors'] = $error;
		}
	}

	public function getLogs(){
		return $this->_logs;
	}

	private function createConnection($host, $script, $params, $mode = false)
	{
		$this->_logs = array();
		$this->_logs[] = 'Making new connection to \''.$host.$script.'\'';
		if (function_exists('curl_exec'))
			$return = $this->_connectByCURL($host.$script, $params);

		if (isset($return) AND $return)
			return $return;

		$tmp = $this->_connectByFSOCK($host, $script, $params);

		if (!$mode || !preg_match('/[A-Z]+=/', $tmp, $result))
			return $tmp;

		$pos = strpos($tmp, $result[0]);
		$retrn = substr($tmp, $pos);
		return $retrn;
	}

	private function _connectByCURL($url, $body)
	{
		$this->_logs = array();
		$ch = @curl_init();
		if (!$ch)
			$this->_logs[] = 'Connect failed with CURL method';
		else
		{
			$this->_logs[] = 'Connect with CURL method successful';
			$this->_logs[] = '<b>Sending this params:</b>';
			$this->_logs[] = $body;
			@curl_setopt($ch, CURLOPT_URL, 'https://'.$url);
			@curl_setopt($ch, CURLOPT_POST, true);
			@curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($ch, CURLOPT_HEADER, false);
			@curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			@curl_setopt($ch,CURLOPT_SSLVERSION, defined(CURL_SSLVERSION_TLSv1) ? CURL_SSLVERSION_TLSv1 : 1);
			@curl_setopt($ch, CURLOPT_VERBOSE, true);
			$result = @curl_exec($ch);
			if (!$result)
				$this->_logs[] = 'Send with CURL method failed ! Error: '.curl_error($ch);
			else
				$this->_logs[] = 'Send with CURL method successful';
			@curl_close($ch);
		}
		return (isset($result) ? $result : false);
	}

	private function _connectByFSOCK($host, $script, $body)
	{
		$this->_logs = array();
		$fp = @fsockopen('tls://'.$host, 443, $errno, $errstr, 4);
		if (!$fp)
			$this->_logs[] = 'Connect failed with fsockopen method';
		else
		{
			$header = $this->_makeHeader($host, $script, strlen($body));
			$this->_logs[] = 'Connect with fsockopen method successful';
			$this->_logs[] = 'Sending this params: '.$header.$body;
			@fputs($fp, $header.$body);
			$tmp = '';
			while (!feof($fp))
				$tmp .= trim(fgets($fp, 1024));
			fclose($fp);
			$result = $tmp;
			if (!$result)
				$this->_logs[] = 'Send with fsockopen method failed !';
			else
				$this->_logs[] = 'Send with fsockopen method successful';
		}
		return (isset($result) ? $result : false);
	}

	private function _makeHeader($host, $script, $lenght)
	{
		$header =	'POST '.strval($script).' HTTP/1.0'."\r\n" .
					'Host: '.strval($host)."\r\n".
					'Content-Type: application/x-www-form-urlencoded'."\r\n".
					'Content-Length: '.(int)($lenght)."\r\n".
					'Connection: close'."\r\n\r\n";
		return $header;
	}

	public function paypalurl()
	{
		return 'www'.($this->apimode == 'sandbox' ? '.sandbox' : '').'.paypal.com';
	}

	private function apiurl()
	{
		return 'api-3t'.($this->apimode == 'sandbox' ? '.sandbox' : '').'.paypal.com';
	}

	private function apiscript()
	{
		return '/nvp';
	}
}
