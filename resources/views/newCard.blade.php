<h4 class="fw-600 mb-3 mt-0">{{ trans('paytr::messages.enter_card') }}</h4>

<?php
$merchant_id = $paytr->gateway->merchant_id;
$merchant_key = $paytr->gateway->merchant_key;
$merchant_salt = $paytr->gateway->merchant_salt;
$merchant_ok_url = action('\Acelle\Paytr\Controllers\PaytrController@success', [
    'invoice_uid' => $invoice->uid,
]);
$merchant_fail_url = action('\Acelle\Paytr\Controllers\PaytrController@failed', [
    'invoice_uid' => $invoice->uid,
]);

$user_basket = htmlentities(json_encode(array(
    // Array items: Product name - Price - Piece
    // array("Basket Example Product", "18.00", 1),
    // array("Basket Example Product", "33,25", 5)
array($invoice->title, $invoice->total(), 1),
)));

srand(time());
$merchant_oid = uniqid() . '0000' . $invoice->uid;
$test_mode = "0";
$non_3d = "0";
$non3d_test_failed = "0";

if (isset($_SERVER["HTTP_CLIENT_IP"])) {
    $ip = $_SERVER["HTTP_CLIENT_IP"];
} elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
    $ip = $_SERVER["REMOTE_ADDR"];
}

$user_ip = $ip;
$email = $invoice->billing_email;
$payment_amount = $invoice->total();
$currency = $invoice->getCurrencyCode();;
$payment_type = "card";

// $card_type = "axess"; // Sent only in installments. Avaliable values; advantage, axess, bonus, cardfinans, maximum, paraf, world
$installment_count = "0"; // 2-12 sent only in installments

$post_url = "https://www.paytr.com/odeme";

$hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $payment_type . $installment_count . $currency . $test_mode . $non_3d;
$token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

## IF THE UTOKEN IS NOT SHIPPED, THIS USER IS ASSUMED THAT THERE IS NOT A PRE-RECORDED CARD.
## AND CREATE A NEW UTOKEN BY PAYTR, IT IS RETURNED IN THE ANSWER OF THE PAYMENT PROCEDURE (TO THE NOTICE URL)!
## If the user has already registered a card in your system, you must add the registered UTOKEN PARAMETER TO THE POST CONTENT.
## THIS CARD WILL BE IDENTIFIED TO THE SAME USER. A NEW CARD FOR THE EXISTING USER
## A NEW UTOKEN WILL BE CREATED IF THERE IS DEFINED, IF IT IS NOT SENT TO UTOKEN, ALL CARD OF THE USER WILL NOT BE GROUPED UNDER ONE UTOKEN !!!

$utoken = "";
?>

<form action="<?php echo $post_url; ?>" method="post">
    <div class="mb-4"> 
        <label class="form-label required">{{ trans('paytr::messages.holder_name') }}</label>
        <input type="text" class="form-control"
            name="cc_owner"
            value=""
        />
    </div>

    <div class="mb-4"> 
        <label class="form-label required">{{ trans('paytr::messages.card_number') }}</label>
        <input type="text" class="form-control"
            name="card_number"
            value=""
        />
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="mb-4"> 
                <label class="form-label required">{{ trans('paytr::messages.expiry_month') }}</label>
                <input type="text" class="form-control"
                    name="expiry_month"
                    value=""
                />
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-4"> 
                <label class="form-label required">{{ trans('paytr::messages.expiry_year') }}</label>
                <input type="text" class="form-control"
                    name="expiry_year"
                    value=""
                />
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-4"> 
                <label class="form-label required">{{ trans('paytr::messages.cvv') }}</label>
                <input type="text" class="form-control"
                    name="cvv"
                    value=""
                />
            </div>
        </div>
    </div>

    <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>">
    <input type="hidden" name="user_ip" value="<?php echo $user_ip; ?>">
    <input type="hidden" name="merchant_oid" value="<?php echo $merchant_oid; ?>">
    <input type="hidden" name="email" value="<?php echo $email; ?>">
    <input type="hidden" name="payment_type" value="<?php echo $payment_type; ?>">
    <input type="hidden" name="payment_amount" value="<?php echo $payment_amount; ?>">
    <input type="hidden" name="installment_count" value="0">
    <input type="hidden" name="currency" value="<?php echo $currency; ?>">
    <input type="hidden" name="test_mode" value="<?php echo $test_mode; ?>">
    <input type="hidden" name="non_3d" value="<?php echo $non_3d; ?>">
    <input type="hidden" name="merchant_ok_url" value="<?php echo $merchant_ok_url; ?>">
    <input type="hidden" name="merchant_fail_url" value="<?php echo $merchant_fail_url; ?>">
    <input type="hidden" name="user_name" value="<?php echo $invoice->getBillingName(); ?>">
    <input type="hidden" name="user_address" value="<?php echo $invoice->billing_address; ?>">
    <input type="hidden" name="user_phone" value="<?php echo $invoice->billing_phone; ?>">
    <input type="hidden" name="user_basket" value="<?php echo $user_basket; ?>">
    <input type="hidden" name="debug_on" value="1">
    <input type="hidden" name="paytr_token" value="<?php echo $token; ?>">
    <input type="hidden" name="non3d_test_failed" value="<?php echo $non3d_test_failed; ?>">
    <input type="hidden" name="installment_count" value="<?php echo $installment_count; ?>">
    <input type="hidden" name="utoken" value="<?php echo $utoken; ?>">
    <input type="hidden" name="store_card" value="1"/>

    <div class="mt-2">
        <input type="submit" value="{{ trans('paytr::messages.submit') }}" class="btn btn-primary">
    </div>
</form>
