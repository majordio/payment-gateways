<?php
class Stripe
{
	private $login_id;
	private $t_key;
 	private $demo;
	private $description;

	public function __construct(){
		$this->secret = STRIPE_SECRET_KEY;
	}

	function makePayment(){
		$amount = $_POST['Amount'] * 100;
		$token = $_POST['stripe_token'];
		$desc = COMPANY_NAME.' - '.FORM_NAME;

		$postfields = array(
			'amount'=> $amount,
			'currency' => 'usd',
			'source' => $token,
			'description' => $desc
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/charges");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $this->secret.":");

		$headers = array();
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close ($ch);

		return json_decode($result);
	}

}
