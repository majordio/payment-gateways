<?php
require_once 'payment-assets/config-payment.php';

$selectedgateways = array_filter($gateways);
$gatewayname = array_keys($selectedgateways);
$gcount = count($selectedgateways);
$btn_text = 'Pay';
if(DONATION){
  $btn_text = 'Donate';
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title>Online Payment Form</title>
<link rel="stylesheet" href="payment-assets/css/bootstrap.min.css">
<link rel="stylesheet" href="payment-assets/css/fa-solid.min.css">
<link rel="stylesheet" href="payment-assets/css/fontawesome.min.css">
<?php if ($gateways['authorize'] || $gateways['payeezy']): ?>
<link rel="stylesheet" href="payment-assets/css/card.css">
<?php endif; ?>
<link rel="stylesheet" href="payment-assets/css/square.css">
<link rel="stylesheet" href="payment-assets/css/style.css">
</head>
	<body>
		<div>
      <div id="coverpage">
        <div class="spinner-box">
          <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
          </div>
          <p>Loading Form</p>
          <p>Please Wait...</p>
        </div>
      </div>

			<form id="paymentform" class="" action="" method="post">
        <input type="hidden" id="is_valid" value="false">
			  <fieldset>
					<br />
						<div class="col-sm-12">
							<div class="form-row">
								<div class="col-sm-12">
								<?php if(TEST_MODE) : ?>
									<div class="alert alert-dismissible alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button><strong><i class="fas fa-info-circle"></i></strong> Test mode is enabled. While in test mode, no live transactions are processed</div>
								<?php endif; ?>
									<div id="alert" hidden>
										<p class="mb-0" id="alert-content"></p>
									</div>
								</div>
							</div>
            <?php if(empty($payments)): ?>
							<?php if(DONATION): ?>
							<div class="form-row">
								<div class="col-sm-12">
										<div class="btn-group-toggle" data-toggle="buttons">
                      <?php if (!empty($donation_amounts)): ?>
                      <?php foreach ($donation_amounts as $amount): ?>
                        <label class="btn btn-lg btn-outline-secondary mb-1">
                          <input type="radio" name="payment" autocomplete="off" value="<?=$amount;?>" /> $<?=$amount;?>
                        </label>
                      <?php endforeach; ?>
                        <label class="btn btn-lg btn-outline-secondary mb-1">
  												<input type="radio" name="payment" autocomplete="off" value="Other" /> Custom Amount
  											</label>
                      <?php endif; ?>
										</div>
								</div>
							</div>
						<?php endif; ?>
							<div class="form-row">
								<div class="col-md-6 mb-3">
									<label for="Amount">Amount</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text" id="AmountPrepend"><i class="fas fa-dollar-sign"></i></span>
										</div>
										<input type="number" step="0.01" min="0.01" name="Amount" class="form-control form-control-lg required" id="Amount" placeholder="0.00" aria-describedby="AmountPrepend">
										<div class="invalid-tooltip">
											Please provide a valid amount
										</div>
									</div>
								</div>
								<?php if(!DONATION): ?>
                  <div class="col-md-6 mb-3">
                    <label for="Payment_For">Payment For</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="Payment_ForPrepend"><i class="fas fa-edit"></i></span>
                      </div>
                      <input type="text" name="Payment_For" class="form-control form-control-lg required" id="Payment_For" placeholder="Enter payment details" aria-describedby="Payment_ForPrepend">
                      <div class="invalid-tooltip">
                        Please enter payment details
                      </div>
                    </div>
                  </div>
								<?php endif; ?>
							</div>
							<?php if(DONATION && RECURRING): ?>
							<div class="form-row">
								<div class="col-sm-12 mb-3">
									<div class="custom-control custom-control-lg custom-checkbox custom-control-inline">
										<input type="checkbox" class="custom-control-input" name="Recurring" id="Recurring" value="true">
										<label class="custom-control-label" for="Recurring">Make this transaction </label>
									</div>
                  <select name="Recurring_Frequency" disabled class="custom-select custom-control-inline col-sm-4 col-md-3 col-lg-2" id="Recurring_Frequency">
                    <option value="Day">Daily</option>
                    <option value="Week">Weekly</option>
                    <option selected value="Month">Monthly</option>
                    <option value="Year">Yearly</option>
                  </select>
								</div>
							</div>
						<?php endif; ?>
            <?php else: ?>
              <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="Payment_For">Payment</label>
                    <div class="input-group">
                      <!-- <div class="input-group-prepend">
                        <span class="input-group-text" id="Payment_ForPrepend"><i class="fas fa-money-bill-alt"></i></span>
                      </div> -->
                      <select class="custom-select form-control-lg required" name="Payment_For">
                        <option value="" selected disabled hidden>Select Payment</option>
                        <?php foreach ($payments as $key => $value): ?>
                          <?php if ($value==''): ?>
                            <option value="<?= $key; ?>" data-amount="<?= $value; ?>"><?= $key; ?></option>
                          <?php elseif(is_array($value)): ?>
                              <optgroup label="<?= $key; ?>">
                            <?php foreach ($value as $key2 => $value2): ?>
                              <?php if ($value2==''): ?>
                                <option value="<?= $key2; ?>" data-amount="<?= $value2; ?>"><?= $key2; ?></option>
                              <?php elseif(strpos($value2, '|') !== false): ?>
                                <?php $minmax2 = explode('|', $value2); ?>
                                <option value="<?= $key2; ?> [$<?= number_format((float)$minmax2[0], 2, '.', ''); ?> - $<?= number_format((float)$minmax2[1], 2, '.', ''); ?>]" data-amount="<?= $value2; ?>"><?= $key2; ?> [$<?= number_format((float)$minmax2[0], 2, '.', ''); ?> - $<?= number_format((float)$minmax2[1], 2, '.', ''); ?>]</option>
                              <?php else: ?>
                                <option value="<?= $key2; ?> [$<?= number_format((float)$value2, 2, '.', ''); ?>]" data-amount="<?= number_format((float)$value2, 2, '.', ''); ?>"><?= $key2; ?> [$<?= number_format((float)$value2, 2, '.', ''); ?>]</option>
                              <?php endif; ?>
                            <?php endforeach; ?>
                            </optgroup>
                          <?php elseif(strpos($value, '|') !== false): ?>
                            <?php $minmax = explode('|', $value); ?>
                            <option value="<?= $key; ?> [$<?= number_format((float)$minmax[0], 2, '.', ''); ?> - $<?= number_format((float)$minmax[1], 2, '.', ''); ?>]" data-amount="<?= $value; ?>"><?= $key; ?> [$<?= number_format((float)$minmax[0], 2, '.', ''); ?> - $<?= number_format((float)$minmax[1], 2, '.', ''); ?>]</option>
                          <?php else: ?>
                            <option value="<?= $key; ?> [$<?= number_format((float)$value, 2, '.', ''); ?>]" data-amount="<?= number_format((float)$value, 2, '.', ''); ?>"><?= $key; ?> [$<?= number_format((float)$value, 2, '.', ''); ?>]</option>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                      <!-- <input type="text" name="Payment_For" class="form-control form-control-lg required" id="Payment_For" placeholder="Enter payment details" aria-describedby="Payment_ForPrepend"> -->
                      <div class="invalid-tooltip">
                        Please select a payment option
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="Amount">Amount <i id="amounttooltip" class="fas fa-question-circle" data-toggle="tooltip" title="Enter amount here." style="display:none;"></i></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="AmountPrepend"><i class="fas fa-dollar-sign"></i></span>
                      </div>
                      <input type="number" step="0.01" min="0.01" name="Amount" class="form-control form-control-lg required" id="Amount" placeholder="0.00" aria-describedby="AmountPrepend" readonly />
                      <div class="invalid-tooltip">
                        Please provide a valid amount
                      </div>
                    </div>
                  </div>
              </div>
          <?php endif; ?>
            <hr>
            <div class="form-row">
  								<div class="col-sm-12">
  									<legend>Personal Information</legend>
  								</div>
  							</div>
                <div class="form-row">
                  <div class="col-md-12 mb-3">
                    <label for="First_Name">Full Name</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id=""><i class="fas fa-user"></i></span>
                      </div>
                      <input type="text" name="First_Name" class="form-control form-control-lg required" id="First_Name" placeholder="First Name">
                      <input type="text" name="Last_Name" class="form-control form-control-lg required" id="Last_Name" placeholder="Last Name">
                      <div class="invalid-tooltip">
                        Please enter your name
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="Email">Email</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id=""><i class="fas fa-envelope"></i></span>
                      </div>
                      <input type="email" name="Email" class="form-control form-control-lg required" id="Email" placeholder="Enter your email address">
                      <div class="invalid-tooltip">
                        Please enter a valid email address
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="Phone">Phone Number</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id=""><i class="fas fa-phone"></i></span>
                      </div>
                      <input type="tel" name="Phone" class="form-control form-control-lg" id="Phone" placeholder="Enter your phone number (optional)">
                      <div class="invalid-tooltip"></div>
                    </div>
                  </div>
								 </div>
                 <div class="form-row">
                   <div class="col-md-12 mb-3">
                     <label for="Additional_Information">Additional Information (optional)</label>
                     <div class="input-group">
                       <div class="input-group-prepend">
                         <span class="input-group-text" id=""><i class="fas fa-sticky-note"></i></span>
                       </div>
                       <textarea name="Additional_Information" class="form-control form-control-lg" id="Additional_Information" placeholder="Enter any additional information" rows="3"></textarea>
                       <div class="invalid-tooltip">

                       </div>
                     </div>
                   </div>
                 </div>
						<hr/>
							<div class="form-row" <?php	if($gcount == 1){echo 'hidden';} ?>>
								<div class="col-sm-12">
									<legend>Payment Gateway</legend>
								</div>
							</div>
							<?php	if($gcount == 0): ?>
								<div class="alert alert-dismissible alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button><strong><i class="fas fa-exclamation-triangle"></i> </strong> No payment gateway(s) enabled</div>
							<?php endif; ?>
							<div class="form-row" <?php	if($gcount == 1){echo 'hidden';} ?>>
								<div class="col-sm-12 mb-3">
										<div class="btn-group-toggle payment-gateway" data-toggle="buttons">
											<?php if ($gateways['paypal']): ?>
												<label class="btn btn-lg btn-outline-primary mb-1">
													<input type="radio" name="gateway" autocomplete="off" value="paypal" <?= (($gcount == 1) && $gateways['paypal'])?'checked':''; ?> />
													<img src="payment-assets/img/paypal.png" alt="PayPal">
												</label>
											<?php endif; ?>
											<?php if ($gateways['authorize']): ?>
											<label class="btn btn-lg btn-outline-primary mb-1">
												<input type="radio" name="gateway" autocomplete="off" value="authorize" <?= (($gcount == 1) && $gateways['authorize'])?'checked':''; ?> />
												<img src="payment-assets/img/authorize.png" alt="Authorize.Net">
											</label>
											<?php endif; ?>
											<?php if ($gateways['stripe']): ?>
											<label class="btn btn-lg btn-outline-primary mb-1">
												<input type="radio" name="gateway" autocomplete="off" value="stripe" <?= (($gcount == 1) && $gateways['stripe'])?'checked':''; ?> />
												<img src="payment-assets/img/stripe.png" alt="Stripe">
											</label>
											<?php endif; ?>
											<?php if ($gateways['payeezy']): ?>
											<label class="btn btn-lg btn-outline-primary mb-1">
												<input type="radio" name="gateway" autocomplete="off" value="payeezy" <?= (($gcount == 1) && $gateways['payeezy'])?'checked':''; ?> />
												<img src="payment-assets/img/payeezy.png" alt="Payeezy">
											</label>
											<?php endif; ?>
                      <?php if ($gateways['square']): ?>
                      <label class="btn btn-lg btn-outline-primary mb-1">
                        <input type="radio" name="gateway" autocomplete="off" value="square" <?= (($gcount == 1) && $gateways['square'])?'checked':''; ?> />
                        <img src="payment-assets/img/square.png" alt="Square">
                      </label>
                      <?php endif; ?>
										</div>
								</div>
							</div>
							<div id="creditcard">
								<div class="form-row">
									<div class="col-md-6">
										<div class="form-row">
											<div class="col-md-12 text-left mb-3">
												<div class="card-wrapper mb-3"></div>
											</div>
										</div>
										<div class="form-row">
											<div class="col-md-12 mb-3">
												<label for="Card_Number">Card Number <i class="fas fa-question-circle" data-toggle="tooltip" title="The (typically) 16 digits on the front of your credit card."></i></label>
												<div class="input-group">
													<div class="input-group-prepend">
												    <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
												  </div>
													<input type="text" name="number" class="form-control form-control-lg required" id="Card_Number" placeholder="•••• •••• •••• ••••">
													<input type="hidden" name="type">
													<div class="invalid-tooltip">
														Please enter your card number.
													</div>
												</div>
											</div>
										</div>
										<div class="form-row">
											<div class="col-md-12 mb-3">
												<label for="Card_FName">Name on Card <i class="fas fa-question-circle" data-toggle="tooltip" title="The name printed on the front of your credit card."></i></label>
												<div class="input-group">
												  <div class="input-group-prepend">
												    <span class="input-group-text" id=""><i class="fas fa-user"></i></span>
												  </div>
												  <input type="text" name="fname" class="form-control form-control-lg required" id="Card_FName" placeholder="First Name">
												  <input type="text" name="lname" class="form-control form-control-lg required" id="Card_LName" placeholder="Last Name">
													<div class="invalid-tooltip">
														Please enter your name.
													</div>
												</div>
											</div>
										</div>
										<div class="form-row">
											<div class="col-md-6 mb-3">
												<label for="Card_Expiry_Date">Expiry Date <i class="fas fa-question-circle" data-toggle="tooltip" title="The date your credit card expires, typically on the front of the card."></i></label>
												<div class="input-group">
													<div class="input-group-prepend">
												    <span class="input-group-text" id=""><i class="fas fa-calendar"></i></span>
												  </div>
													<input type="text" name="expiry" class="form-control form-control-lg required" id="Card_Expiry_Date" placeholder="MM / YYYY">
													<div class="invalid-tooltip">
														Please enter your card expiry date.
													</div>
												</div>
											</div>
											<div class="col-md-6 mb-3">
												<label for="Card_Security_Code">Security Code <i class="fas fa-question-circle" data-toggle="tooltip" title="The 3 digit (back) or 4 digit (front) value on your card."></i></label>
												<div class="input-group">
													<div class="input-group-prepend">
												    <span class="input-group-text" id=""><i class="fas fa-lock"></i></span>
												  </div>
													<input type="text" name="cvc" class="form-control form-control-lg required" id="Card_Security_Code" placeholder="CVV/CVC">
													<div class="invalid-tooltip">
														Please enter your card security code (CVV/CVC).
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

              <div id="squareform" style="display:none;">
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="Card_Number">Card Number <i class="fas fa-question-circle" data-toggle="tooltip" title="The (typically) 16 digits on the front of your credit card."></i></label>
                      <div id="sq-card-number"></div>
                    </div>
                </div>
                <div class="form-row">
                  <div class="col-md-3 mb-3">
                    <label for="Card_Security_Code">Security Code <i class="fas fa-question-circle" data-toggle="tooltip" title="The 3 digit (back) or 4 digit (front) value on your card."></i></label>
                      <div id="sq-cvv"></div>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="Card_Expiry_Date">Expiry Date <i class="fas fa-question-circle" data-toggle="tooltip" title="The date your credit card expires, typically on the front of the card."></i></label>
                      <div id="sq-expiration-date"></div>
                  </div>
                </div>
                <div class="form-row">
                  <div class="col-md-6 mb-3">
                    <label for="Postal_Code">Postal Code <i class="fas fa-question-circle" data-toggle="tooltip" title="Enter your postal code."></i></label>
                      <div id="sq-postal-code"></div>
                    </div>
                </div>
              </div>


								<div id="btn-row" class="form-row">
                                           <div class="col-md-4 col-sm-6">
                                             <div class="form-group">
                                               <div class="input-group">
                                                 <div class="input-group-prepend">
                                                   <span class="input-group-text" id="AmountPrepend"><b>Total:</b></span>
                                                 </div>
                                                 <input type="text" class="form-control form-control-lg text-right amount" value="$0.00" style="font-weight:bold;" disabled>
                                                 <div class="input-group-append recurring" style="display:none;">
                                                   <span class="input-group-text" id="recurringfreq"></span>
                                                 </div>
                                               </div>
                                             </div>
                                           </div>
									<div class="col-md-4 col-sm-6">
										<div class="form-group">
                                                       <div id="paypal-button-container" hidden></div>
											<button id="btn-submit-id" class="btn btn-lg btn-primary btn-lg btn-submit" type="submit" name="method"><i class="fas fa-lock"></i> <?=$btn_text;?> Now</button>
										</div>
									</div>
								</div>
              </div>


          <input type="hidden" id="card-nonce" name="nonce">
				</fieldset>
			</form>
		</div>

    <?php if ($gateways['paypal']): ?>
      <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    <?php endif; ?>
		<script src="payment-assets/js/jquery-3.3.1.min.js"></script>
    <?php if ($gateways['authorize'] || $gateways['payeezy']): ?>
		    <script src="payment-assets/js/jquery.card.js"></script>
    <?php endif; ?>
		<script src="payment-assets/js/popper.min.js"></script>
		<script src="payment-assets/js/bootstrap.min.js"></script>
    <?php if ($gateways['stripe']): ?>
    <script src="https://checkout.stripe.com/checkout.js"></script>
    <?php endif; ?>
    <?php if ($gateways['square']): ?>
    <script src="https://js.squareup.com/v2/paymentform"></script>
    <?php endif; ?>
		<script type="text/javascript">
			$(document).ready(function(){
        var amount = 0;

        $('[data-toggle="tooltip"]').tooltip();

        <?php if(!empty($payments)): ?>
          $('select[name="Payment_For"]').on('change', function(){
            var amt = $(this).find('option:selected').data('amount');
            $('#amounttooltip').attr('data-original-title','Enter amount here').hide();
            $('input[name="Amount"]').attr('min','0.01').removeAttr('max');
            if(amt==''){
              $('#Amount').prop('readonly', false);
              $('#amounttooltip').show();
              setTimeout(function(){ $('input[name="Amount"]').focus().select(); }, 0);
            }else if(amt.indexOf('|') != -1){
              $('#Amount').prop('readonly', false);
              var minmax = amt.split('|');
              $('#amounttooltip').attr('data-original-title','Enter amount between $'+minmax[0]+' and $'+minmax[1]).show();
              $('input[name="Amount"]').attr('min',minmax[0]).attr('max',minmax[1]);
              setTimeout(function(){ $('input[name="Amount"]').focus().select(); }, 0);
            }else {
              $('#Amount').prop('readonly', true);
            }
            setamount(amt);
          });
        <?php endif; ?>
        <?php if ($gateways['authorize'] || $gateways['payeezy']): ?>
        document.getElementsByName('number')[0].addEventListener('payment.cardType', function(e) {
          var card_type;
          switch (e.detail) {
            case 'amex':
              card_type = 'A';
              break;
            case 'mastercard':
              card_type = 'M';
              break;
            case 'visa':
              card_type = 'V';
              break;
            case 'discover':
              card_type = 'D';
              break;
            default:
              card_type = '';
          }
          $('input[name="type"]').val(card_type);
        });
        $('#creditcard input').prop('disabled',true);
				$('form').card({
          width: 320,
					form: 'form',
					container: '.card-wrapper',
					formSelectors: {
					   nameInput: 'input[name="fname"], input[name="lname"]'
					},
					placeholders: {
							number: '•••• •••• •••• ••••',
							name: 'Name on Card',
							expiry: '••/••••',
							cvc: '•••'
					}
					});
        <?php endif; ?>
        <?php if ($gateways['square']): ?>
        var paymentForm = new SqPaymentForm({
          applicationId: "<?= SQUARE_APPLICATION_ID; ?>",
          locationId: "<?= SQUARE_LOCATION_ID; ?>",
          inputClass: 'sq-input',
          autoBuild: false,
          inputStyles: [{
            fontSize: '18px',
            fontFamily: 'Arial',
            padding: '15px',
            color: '#373F4A',
            lineHeight: '24px',
            placeholderColor: '#BDBFBF'
          }],

          cardNumber: {
            elementId: 'sq-card-number',
            placeholder: '•••• •••• •••• ••••'
          },
          cvv: {
            elementId: 'sq-cvv',
            placeholder: 'CVV'
          },
          expirationDate: {
            elementId: 'sq-expiration-date',
            placeholder: 'MM/YY'
          },
          postalCode: {
            elementId: 'sq-postal-code'
          },
          callbacks: {
            cardNonceResponseReceived: function(errors, nonce, cardData, billingContact, shippingContact) {
              if (errors) {
                // Log errors from nonce generation to the Javascript console
                var errorlist = '<b>Error(s):</b><br>';
                errors.forEach(function(error) {
                  errorlist += error.message+'<br>';
                });
                $('#alert').removeClass().attr('hidden', false).addClass('alert alert-danger').show();
                $('#alert p').html('<b><i class="fas fa-exclamation-triangle"></i></b> '+errorlist);

                return;
              }
              $('#paymentform').prepend('<input type="hidden" name="square_token" value="'+nonce+'" />');
              getajax();
              $('input[name="square_token"]').remove();

            },
            paymentFormLoaded: function() {

            }
          }
        });
        function squarehandler() {
          paymentForm.requestCardNonce();
        }
        <?php endif; ?>
				<?php	if($gcount == 1): ?>
					setgateway('<?=$gatewayname[0];?>');
				<?php	endif; ?>
        <?php	if($gcount > 1): ?>
				$('input[name="gateway"]').on('change',function(){
					var gateway = $(this).val();
					setgateway(gateway);
				});
        <?php	endif; ?>
        function setgateway(gateway){
					if(gateway=='paypal'){
            $('#paypal-button-container').prop('hidden',false);
            $('#squareform').fadeOut();
            $('.btn-submit').hide();
						$('#creditcard').fadeOut();
						$('#creditcard input').prop('disabled',true);
					}else if(gateway=='authorize' || gateway=='payeezy'){
            $('#squareform').fadeOut();
            $('#paypal-button-container').prop('hidden',true);
            $('.btn-submit').show();
						$('#creditcard').fadeIn();
						$('#creditcard input').prop('disabled',false);
					}else if(gateway=='stripe'){
            $('#squareform').fadeOut();
            $('#paypal-button-container').prop('hidden',true);
            $('.btn-submit').show();
						$('#creditcard').fadeOut();
						$('#creditcard input').prop('disabled',true);
          }else if(gateway=='square'){
            $('#paypal-button-container').prop('hidden',true);
            $('#squareform').fadeIn();
            $('.btn-submit').show();
            $('#creditcard').fadeOut();
            $('#creditcard input').prop('disabled',true);
            if(!$('.sq-input')[0]){
              paymentForm.build();
            }
          }else{
            $('#paypal-button-container').prop('hidden',true);
            $('#squareform').fadeOut();
            $('.btn-submit').show();
						$('#creditcard').fadeOut();
						$('#creditcard input').prop('disabled',true);
					}
					$('.btn-submit').val(gateway);
					$('#btn-row').fadeIn();
				}
        <?php if(DONATION && RECURRING): ?>
        $('input[name="Recurring"]').on('change',function(){
					if($(this).is(':checked')){
              $('.recurring').show();
              $('#recurringfreq').html('<b>'+$('select[name="Recurring_Frequency"]').find('option:selected').text()+'</b>');
              $('select[name="Recurring_Frequency"]').prop('disabled',false);
          }else {
            $('select[name="Recurring_Frequency"]').prop('disabled',true);
            $('.recurring').hide();
          }
				});
        $('select[name="Recurring_Frequency"]').on('change',function(){
          $('#recurringfreq').html('<b>'+$(this).find('option:selected').text()+'</b>');
				});
        <?php endif; ?>

        <?php if(DONATION && !empty($donation_amounts)): ?>
  				$('input[name="payment"]').on('change', function(){
  					var amount = $('input[name="payment"]:checked').val();
  					if(amount!='Other'){
              setamount(amount);
  					}else {
  						setTimeout(function(){ $('input[name="Amount"]').focus().select(); }, 0);
  					}
  				});
          <?php endif; ?>

				$('input[name="Amount"]').on('change', function(){
					// if($(this).val()){
						setamount($(this).val());
						<?php if(DONATION): ?>
						$('input[name="payment"][value="Other"]').trigger('click');
						<?php endif; ?>
					// }
				});

        function setamount(num){
          if(num=='')
            amount = 0;
          else if(num.indexOf("|") >= 0)
            amount = num.split('|')[0];
          else
            amount = num;
          $('input[name="Amount"]').val(parseFloat(amount).toFixed(2));
          $('.btn-submit').html('<i class="fas fa-lock"></i> <?=$btn_text; ?> $'+amount);
          $('.amount').val('$'+parseFloat(amount).toFixed(2));
        }

        function showsuccess(trans){
          $('#coverpage').html('<div class="successpage col-md-12 text-center"><div class="mt-4"><i class="fas fa-check-circle fa-9x text-success" ></i><h2>Your transaction with the amount of $'+amount+' was completed successfully</h2><h3 class="mb-5">Transaction ID: <b>'+trans+'</b></h3><p><a href="" class="btn btn-primary btn-lg">Go Back</a></p></div></div>');
          $('#coverpage').fadeIn();
        }

        function getajax(){
          var buttontext = $('.btn-submit').html();
					$('.btn-submit').html('Please Wait <i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
          var formdata = $('#paymentform').serialize();
          $.ajax({
            type: "POST",
            url: "<?php (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>ajax-payment.php",
            data: formdata,
            success: function(result){
              $('.btn-submit').html(buttontext).prop('disabled', false);
              $('html,body').animate({scrollTop:0},500);
							if(is_json(result)){
                var obj = JSON.parse(result);
                $('.required').attr('required', true);
                $('#paymentform').addClass('was-validated');
                $('#alert').removeClass().attr('hidden', false).addClass('alert alert-'+obj.status);
								$('#alert p').html(obj.response);
								if(obj.link){
                  getlink(obj.link);
								}
                if(obj.status == 'success'){
                  showsuccess(obj.link);
                }
              }else {
                $('#alert').removeClass().attr('hidden', false).addClass('alert alert-warning');
                $('#alert p').html(result);
              }
			    	},
            error: function(xhr){
              $('.btn-submit').html(buttontext).prop('disabled', false);
              $('html,body').animate({scrollTop:0},500);
              $('#alert').removeClass().attr('hidden', false).addClass('alert alert-danger');
              $('#alert p').html('<b>Error: </b> No response from the server.');
            }
          });

        }

        function getlink(link){
          if($('.btn-submit').val() == 'stripe' && link == 'token'){
              stripehandler();
          }else if ($('.btn-submit').val() == 'square' && link == 'token') {
            squarehandler();
          }
        }
        $('#paymentform').on('submit', function(e){
					e.preventDefault();
          getajax();
				});

        function is_json(variable){
          var IS_JSON = true;
           try
           {
              var obj = JSON.parse(variable);
           }
           catch(err)
           {
              IS_JSON = false;
           }
           return IS_JSON;
        }

        function getFormData($form){
          var unindexed_array = $form.serializeArray();
          var indexed_array = {};

          $.map(unindexed_array, function(n, i){
              indexed_array[n['name']] = n['value'];
          });

          return indexed_array;
        }


    <?php if ($gateways['paypal']): ?>
        paypal.Button.render({
            env: '<?= (TEST_MODE)?'sandbox':'production' ?>',
            commit: true,
            style: {
              // label: 'pay',
              size:  'responsive', // small | medium | large | responsive
              shape: 'rect',   // pill | rect
              color: 'gold',   // gold | blue | silver | black
              layout: 'vertical',
              // fundingicons: 'true'
            },
            funding: {
              allowed: [ paypal.FUNDING.CARD  ],
              disallowed: [ paypal.FUNDING.CREDIT ]
            },
            payment: function(data, actions) {
              // return actions;
                // Set up a url on your server to create the payment
                var CREATE_URL = "<?php (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>ajax-payment.php?paypal=direct";
                // Make a call to your server to set up the payment
                 var formdata = getFormData($('#paymentform'));
                   return paypal.request({method:'post',url:CREATE_URL, data: formdata}).then(function(obj) {
                     $('html,body').animate({scrollTop:0},500);
      							if(obj.status!= undefined){
                      if(obj){
        								$('#alert').removeClass().attr('hidden', false).addClass('alert alert-'+obj.status);
        								$('#alert p').html(obj.response);
        								if(obj.validate){
        									$('.required').attr('required', true);
        									$('#paymentform').addClass('was-validated');
        								}
        								if(obj.link){
        									return obj.link;
        								}
                        try{
                          return false
                        }catch(err){
                          console.log(err);
                        }

        							}
                    }else {
                      $('#alert').removeClass().attr('hidden', false).addClass('alert alert-warning');
                      $('#alert p').html(obj);
                    }
                });
            },
            // onAuthorize() is called when the buyer approves the payment
            onAuthorize: function(data, actions) {
                // Set up a url on your server to execute the payment
                var EXECUTE_URL = "<?php (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>ajax-payment.php?paypal=return";
                // Set up the data you need to pass to your server
                var data = {
                    paymentID: data.paymentToken,
                    payerID: data.payerID,
                    gateway: 'paypal'
                };
                // Make a call to your server to execute the payment
                return paypal.request.post(EXECUTE_URL, data)
                    .then(function (obj) {
                      $('html,body').animate({scrollTop:0},500);
                         if(obj.status!= undefined){
                           if(obj){
                             $('#alert').removeClass().attr('hidden', false).addClass('alert alert-'+obj.status).show();
                             $('#alert p').html(obj.response);
                             if(obj.validate){
                               $('.required').attr('required', true);
                               $('#paymentform').addClass('was-validated');
                             }
                             if(obj.status == 'success'){
                               showsuccess(obj.link);
                             }
                             if(obj.link){
                               return obj.link;
                             }
                             return false;
                           }
                         }else {
                           $('#alert').removeClass().attr('hidden', false).addClass('alert alert-warning').show();
                           $('#alert p').html(obj);
                         }
                    });
            },
            onCancel: function(){
              $('#alert').removeClass().attr('hidden', false).addClass('alert alert-warning').show();
              $('#alert p').html('<b><i class="fas fa-exclamation-triangle"></i></b> Payment has been cancelled.');
            }
        }, '#paypal-button-container');
    <?php endif; ?>


    <?php if ($gateways['stripe']): ?>
    var token_triggered = false;
    var handler = StripeCheckout.configure({
      key: '<?= STRIPE_PUBLISHABLE_KEY; ?>',
      // image: 'payment-assets/img/stripe.png',
      token: function(token, args) {
        token_triggered = token.id;
        $('#paymentform').prepend('<input type="hidden" name="stripe_token" value="'+token_triggered+'" />');
        getajax();
        $('input[name="stripe_token"]').remove();
      },
      closed: function() {
        if (token_triggered) {
          $('#alert').removeClass().attr('hidden', false).addClass('alert alert-info').show();
          $('#alert p').html('<b><i class="fas fa-spinner fa-spin"></i></b> Please Wait...');
         } else {
           $('#alert').removeClass().attr('hidden', false).addClass('alert alert-info').show();
           $('#alert p').html('<b><i class="fas fa-exclamation-triangle"></i></b> Payment has been cancelled.');
         }
       }
    });

    $(window).on('popstate', function() {
      handler.close();
    });

    function stripehandler(){
      handler.open({
        name: '<?= COMPANY_NAME; ?>',
        description: '<?= FORM_NAME; ?>',
        amount: amount * 100
      });
    }
      <?php endif; ?>
      $('#coverpage').fadeOut();
});
		</script>
	</body>
</html>
