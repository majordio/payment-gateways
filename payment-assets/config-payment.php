<?php
/*
* NOTE:
* Payment Form
* Version: 4.4
*/

ini_set('display_errors', false);
ini_set('display_startup_errors', false);
error_reporting(E_ALL);

////////////////////////////////////////////////////////

define('TEST_MODE', true);
define('COMPANY_NAME', 'COMPANY_NAME');
define('FORM_NAME', 'Donation/Sponsorship/Payment');

  define('DONATION', true);



if(DONATION){
  define('RECURRING', true);
  $donation_amounts = array(10,25,50,100,200);
}else {
  $payments = array(
    // 'SamplePayment' => '1.00',
    // 'PaymentWithMinMax' => '2.00|5.00',
    // 'PaymentWithGroup' => array(
    //   'SampleInsideGroup1' => '10.00|100.00',
    //   'SampleInsideGroup2' => '5.00',
    //   'SampleInsideGroupOther' => ''
    // ),
    // 'Other' => ''
  );
}

$gateways = array(
  'paypal'    => false,
  'authorize' => false,
  'payeezy'   => false,
  'stripe'    => false,
  'square'    => false
);

$required = array('First_Name','Last_Name','Email');

if(TEST_MODE){
  //NOTE: PAYPAL
  define("PAYPAL_USERNAME", "sp-facilitator_api1.proweaver.net");
  define("PAYPAL_PASSWORD", "1399873650");
  define("PAYPAL_SIGNATURE", "AiPC9BjkCyDFQXbSkoZcgqH3hpacA74rJq85b-pTFAHAZ.71hb30iH12");

  //NOTE: AUTHORIZE.NET
  define('AUTHORIZE_LOGINID','9r52BTU4dyJ');
  define('AUTHORIZE_TKEY','83JU8Mjs7n2g8r4p');

  //NOTE: PAYEEZY
  define('PAYEEZY_GATEWAYID','PJ8599-42');
  define('PAYEEZY_PASSWORD','NfuhB9IRbAkEdyN2uavBV5uH3TBviVaZ');
  define('PAYEEZY_KEYID','557015');
  define('PAYEEZY_HMACKEY','OHicnLnzcRcMTAdkhMv27KJVwYT8m3DO');

  //NOTE: STRIPE
  define('STRIPE_PUBLISHABLE_KEY','pk_test_Y2XsAOSNSABe2aOW2Y4s1B0x');
  define('STRIPE_SECRET_KEY','sk_test_O2dUx2u1rnMoIEAbjo1fxhzJ');

  //NOTE: SQUARE
  define('SQUARE_APPLICATION_ID','sandbox-sq0idp-Cv-UCD-d58VKczDvd8PXog');
  define('SQUARE_ACCESS_TOKEN','sandbox-sq0atb-s3f9nBf4GmWjTLcRoEm9aw');
  define('SQUARE_LOCATION_ID','CBASEICWWAw5k2TCC_Ez-1bJueUgAQ');

}else {

  //NOTE: PAYPAL
  define('PAYPAL_USERNAME','');
  define('PAYPAL_PASSWORD','');
  define('PAYPAL_SIGNATURE','');


  //NOTE: AUTHORIZE.NET
  define('AUTHORIZE_LOGINID','');
  define('AUTHORIZE_TKEY','');

  //NOTE: PAYEEZY
  define('PAYEEZY_GATEWAYID','');
  define('PAYEEZY_PASSWORD','');
  define('PAYEEZY_KEYID','');
  define('PAYEEZY_HMACKEY','');

  //NOTE: STRIPE
  define('STRIPE_PUBLISHABLE_KEY','');
  define('STRIPE_SECRET_KEY','');

  //NOTE: SQUARE
  define('SQUARE_APPLICATION_ID','');
  define('SQUARE_ACCESS_TOKEN','');
  define('SQUARE_LOCATION_ID','');
}
