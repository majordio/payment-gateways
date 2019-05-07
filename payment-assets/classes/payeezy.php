<?php
class Payeezy {
    public $cc_name;
    public $cc_number;
    public $cc_cvv;
    public $cc_month;
    public $cc_year;
    public $email;
    public $transaction_id;
    public $amount;

    public function makePayment() {
				$expiry = explode('/', $_POST['expiry']);
			  $exp = trim($expiry[0]).substr(trim($expiry[1]), -2);
				$card_num = str_replace(' ','',$_POST['number']);
        if(TEST_MODE){
            $endpoint = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v12';

        }else{
            $endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
        }
        $myorder = array(
            'gateway_id' => PAYEEZY_GATEWAYID,
            'password' => PAYEEZY_PASSWORD,
            'transaction_type' => '00',
            'amount' => $_POST['Amount'],
            'cardholder_name' => $_POST['fname'].' '.$_POST['lname'],
            'cc_number' => $card_num,
            'cc_expiry' => $exp,
            'cvd_code' => $_POST['cvc'],
            'client_ip' => '192.168.1.2',
            'client_email' => $_POST['Email']

        );

				// print_r($myorder);

        $data_string = json_encode($myorder);

        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL,$endpoint);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_VERBOSE, 1);
        curl_setopt ($ch, CURLOPT_POST, true);


        $content_digest = sha1($data_string);

        $current_time = gmdate('Y-m-dTH:i:s') . 'Z';
        $current_time = str_replace('GMT', 'T', $current_time);

        $code_string = "POST\napplication/json\n{$content_digest}\n{$current_time}\n/transaction/v12";
        $code = base64_encode(hash_hmac('sha1',$code_string,PAYEEZY_HMACKEY,true));

        $header_array = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'X-GGe4-Content-SHA1: '. $content_digest,
            'X-GGe4-Date: ' . $current_time,
            'Authorization: GGE4_API ' . PAYEEZY_KEYID . ':' . $code,
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        $result = curl_exec ($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
				$result = json_decode($result);

        return array('status'=>$status,'result'=>$result);
    }
}
