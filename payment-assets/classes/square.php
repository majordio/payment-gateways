<?php
class Square
{
	private $access_token;
	private $location_id;

	public function __construct(){
		$this->access_token = SQUARE_ACCESS_TOKEN;
    $this->location_id = SQUARE_LOCATION_ID;
	}

	function makePayment(){
		$amount = $_POST['Amount'] * 100;
		$token = $_POST['square_token'];
		$desc = COMPANY_NAME.' - '.FORM_NAME;

		$postfields = array(
      'card_nonce' => $token,
			'amount_money'=> array(
        'amount'=>$amount,
        'currency' => 'USD'
      ),
      'idempotency_key' => time().urlencode($_POST['Last_Name'])
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://connect.squareup.com/v2/locations/".$this->location_id."/transactions");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
		curl_setopt($ch, CURLOPT_POST, 1);

		$headers = array();
    $headers[] = "Accept: application/json";
		$headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer ".$this->access_token;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close ($ch);

		return json_decode($result);
	}

}
