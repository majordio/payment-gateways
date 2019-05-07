<?php
class Authorize
{
	private $login_id;
	private $t_key;
 	private $demo;
	private $description;

	public function __construct(){
		$this->login_id = AUTHORIZE_LOGINID;
		$this->t_key = AUTHORIZE_TKEY;
		$this->demo = TEST_MODE;
		$this->description = FORM_NAME;
	}

	function makePayment(){

		  $expiry = explode('/', $_POST['expiry']);
		  $exp = trim($expiry[0]).substr(trim($expiry[1]), -2);
		  $card_num = str_replace(' ','',$_POST['number']);
		  $card_code = $_POST['cvc'];

		  $params = array(
		    'x_invoice_num' => $_POST['fname'][0].$_POST['lname'][0].time(),
		    'x_amount' => $_POST['Amount'],
		    'x_description'	=> $this->description,
		    'x_exp_date' => $exp,
		    'x_first_name' => $_POST['fname'],
		    'x_last_name' => $_POST['lname'],
		    'x_email' => $_POST['Email'],
		    'x_phone' => $_POST['Phone'],
		    'x_version' => '3.1',
		    'x_delim_data' => true,
		    'x_delim_char' => '|',
		    'x_relay_response' => false,
		    'x_type' => 'AUTH_CAPTURE',
		    'x_method' => 'CC',
		    'x_solution_id' => 'A1000006',
		    'x_login' => $this->login_id,
		    'x_tran_key' => $this->t_key,
		    'x_card_num' => $card_num,
		    'x_card_code' => $card_code,
		    );

		  $postString = '';
		  foreach ($params as $key => $value)
		    $postString .= $key.'='.urlencode($value).'&';
		  $postString = trim($postString, '&');

		  //foreach( $line_items as $value )
		    //{ $postString .= "&x_line_item=" . urlencode( $value ); }

		  $url = 'https://secure.authorize.net/gateway/transact.dll';
		  if ($this->demo)
		  {
		    $postString .= '&x_test_request=TRUE';
		    $url = 'https://test.authorize.net/gateway/transact.dll';
		  }
		  /* Do the CURL request ro Authorize.net */
		  $request = curl_init($url);
		  curl_setopt($request, CURLOPT_HEADER, 0);
		  curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($request, CURLOPT_POSTFIELDS, $postString);
		  curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		  $postResponse = curl_exec($request);
		  curl_close($request);

		  $response = explode('|', $postResponse);

			return $response;
	}

}
