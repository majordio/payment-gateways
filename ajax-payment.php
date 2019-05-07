<?php
@session_start();
require_once 'payment-assets/config-payment.php';
require_once 'functions.php';

if(!empty($_POST)){
  if(!validateData($_POST, $required)){
    echo json_encode(message('danger','<b><i class="fas fa-times-circle"></i></b> Please fill in the required fields.',null,'has_errors'));
    exit;
  }
  
    switch ($_POST['gateway']) {
      case 'paypal':
        if($_GET['paypal'] == 'direct')
          echo json_encode(paypaldirect());
        else
          echo json_encode(paypalreturn());
        break;
      case 'authorize':
        echo json_encode(authorizepayment());
        break;
      case 'payeezy':
        echo json_encode(payeezypayment());
        break;
      case 'stripe':
        if(isset($_POST['stripe_token']))
          echo json_encode(stripepayment());
        else
          echo json_encode(message('info','<b><i class="fas fa-spinner fa-spin"></i></b> Processing Payment...','token'));
        break;
      case 'square':
        if(isset($_POST['square_token']))
          echo json_encode(squarepayment());
        else
          echo json_encode(message('info','<b><i class="fas fa-spinner fa-spin"></i></b> Processing Payment...','token'));
        break;
      default:
        echo json_encode(message('danger','<b><i class="fas fa-times-circle"></i></b> No Payment Gateway Selected.'));
        exit;
      break;
    }

  exit;
}

if(!empty($_GET)){
  if($_GET['gateway']){
    echo paypaldirecttest();
  }else {
    echo paypaldirecttest();
  }
}

function validateData($data, $required){
  array_push($required,'Amount');
  if($data['gateway'] == 'authorize' || $data['gateway'] == 'payeezy'){
    array_push($required,"number","fname","lname","expiry","cvc");
  }
  foreach ($data as $key => $value) {
    if(!in_array($key,$required)) continue;
    if(empty($value)){
      return false;
    }
  }
  return true;
}
