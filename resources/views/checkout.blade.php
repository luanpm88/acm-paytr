<html lang="en">
    <head>
        <title>{{ trans('paytr::messages.paytr') }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <link rel="stylesheet" href="{{ \Acelle\Cashier\Cashier::public_url('/vendor/acelle-cashier/css/main.css') }}">
    </head>
    
    <body>
        <div class="main-container row mt-40">
            <div class="col-md-2"></div>
            <div class="col-md-4 mt-40 pd-60">
                <img class="rounded" width="80%" src="{{ $paytr->plugin->getIconUrl() }}" />
            </div>
            <div class="col-md-4 mt-40 pd-60">                
                <h2 class="mb-40">{{ $invoice->title }}</h2>
                <p>{!! trans('paytr::messages.checkout.intro', [
                    'price' => format_price($invoice->total(), $invoice->currency->format),
                ]) !!}</p>




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
)));

srand(time());
$merchant_oid = $invoice->uid;
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

$card_type = "axess"; // Sent only in installments. Avaliable values; advantage, axess, bonus, cardfinans, maximum, paraf, world
$installment_count = "5"; // 2-12 sent only in installments

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
    Card Holder Name: <input type="text" name="cc_owner" value="TEST KARTI"><br>
    Card Number: <input type="text" name="card_number" value="4355084355084358"><br>
    Card Month: <input type="text" name="expiry_month" value="12"><br>
    Card Year: <input type="text" name="expiry_year" value="99"><br>
    CVV: <input type="text" name="cvv" value="000"><br>
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
    <input type="hidden" name="user_name" value="Paytr Test">
    <input type="hidden" name="user_address" value="test test test">
    <input type="hidden" name="user_phone" value="05555555555">
    <input type="hidden" name="user_basket" value="<?php echo $user_basket; ?>">
    <input type="hidden" name="debug_on" value="1">
    <input type="hidden" name="paytr_token" value="<?php echo $token; ?>">
    <input type="hidden" name="non3d_test_failed" value="<?php echo $non3d_test_failed; ?>">
    <input type="hidden" name="installment_count" value="<?php echo $installment_count; ?>">
    <input type="hidden" name="card_type" value="<?php echo $card_type; ?>">
    <input type="hidden" name="utoken" value="<?php echo $utoken; ?>">
    <input type="checkbox" name="store_card" value="1"/> Store My Card
    <br/>
    <input type="submit" value="Submit">
</form>


                <?php


// $merchant_id = $paytr->gateway->merchant_id;
//                 $merchant_key = $paytr->gateway->merchant_key;
//                 $merchant_salt = $paytr->gateway->merchant_salt;

//     $bin_number = "97920303";

//     $hash_str = $bin_number . $merchant_id . $merchant_salt;
//     $paytr_token=base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));
//     $post_vals=array(
//         'merchant_id'=>$merchant_id,
//         'bin_number'=>$bin_number,
//         'paytr_token'=>$paytr_token
//     );

//     $ch=curl_init();
//     curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/bin-detail");
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_POST, 1) ;
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
//     curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 20);

//     $result = @curl_exec($ch);

//     if(curl_errno($ch))
//         die("PAYTR BIN detail request timeout. err:".curl_error($ch));

//     curl_close($ch);

//     $result=json_decode($result,1);

//     if($result['status']=='error')
//         die("PAYTR BIN detail request error. Error:".$result['err_msg']);
//     elseif($result['status']=='failed')
//         die("BIN tanımlı değil. (Örneğin bir yurtdışı kartı)");
//     else
//         print_r($result);
//         die();
?>

<?php



                // $merchant_id = $paytr->gateway->merchant_id;
                // $merchant_key = $paytr->gateway->merchant_key;
                // $merchant_salt = $paytr->gateway->merchant_salt;
            
                // $email = $invoice->billing_email;
                // $payment_amount	= $invoice->total();
                // $merchant_oid = $invoice->uid;
                // $user_name = $invoice->billing_first_name . ' ' . $invoice->billing_last_name;
                // $user_address = $invoice->billing_address;
                // $user_phone = $invoice->billing_phone;
                // $merchant_ok_url = action('\Acelle\Paytr\Controllers\PaytrController@success', [
                //     'invoice_uid' => $invoice->uid,
                // ]);
                // $merchant_fail_url = action('\Acelle\Paytr\Controllers\PaytrController@failed', [
                //     'invoice_uid' => $invoice->uid,
                // ]);
                // $user_basket = htmlentities(json_encode(array(
                //     array($invoice->title, $invoice->total(), 1),
                // )));

                // $merchant_id = $paytr->gateway->merchant_id;
                // $merchant_key = $paytr->gateway->merchant_key;
                // $merchant_salt = $paytr->gateway->merchant_salt;

                // $merchant_ok_url = action('\Acelle\Paytr\Controllers\PaytrController@success', [
                //     'invoice_uid' => $invoice->uid,
                // ]);
                // $merchant_fail_url = action('\Acelle\Paytr\Controllers\PaytrController@failed', [
                //     'invoice_uid' => $invoice->uid,
                // ]);

                // $user_basket = htmlentities(json_encode(array(
                //     array($invoice->title, $invoice->total(), 1),
                // )));

                // srand(time());
                // $merchant_oid = rand();
                // $test_mode="1";
                // $non_3d="0";
                // $non3d_test_failed="0";
                // if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
                //     $ip = $_SERVER["HTTP_CLIENT_IP"];
                // } elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
                //     $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                // } else {
                //     $ip = $_SERVER["REMOTE_ADDR"];
                // }
                // $user_ip = $ip;
                // $email = $invoice->billing_email;
                // $payment_amount = $invoice->total();
                // $currency=$invoice->getCurrencyCode();
                // $payment_type = "card";
                // $client_lang = "en"; // tr or en

                // 		$card_type = "maximum";       // advantage, axess, combo, bonus, cardfinans, maximum, paraf, world
                // 		$installment_count = "0";

                // $post_url = "https://www.paytr.com/odeme";

                // $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $payment_type . $installment_count. $currency. $test_mode. $non_3d;
                // $token = base64_encode(hash_hmac('sha256',$hash_str.$merchant_salt,$merchant_key,true));
                ?>

                {{-- <form action="<?php echo $post_url;?>" method="post">
                Card Holder Name: <input type="text" name="cc_owner" value="TEST CARD"><br>
                Card Number: <input type="text" name="card_number" value="5406675406675403"><br>
                Card Expiration Month: <input type="text" name="expiry_month" value="12" ><br>
                Card Expiration Year: <input type="text" name="expiry_year" value="99"><br>
                Card Security Code: <input type="text" name="cvv" value="000"><br>
                <input type="hidden" name="merchant_id" value="<?php echo $merchant_id;?>">
                <input type="hidden" name="user_ip" value="<?php echo $user_ip;?>">
                <input type="hidden" name="merchant_oid" value="<?php echo $merchant_oid;?>">
                <input type="hidden" name="email" value="<?php echo $email;?>">
                <input type="hidden" name="payment_type" value="<?php echo $payment_type;?>">
                <input type="hidden" name="payment_amount" value="<?php echo $payment_amount;?>">
                <input type="hidden" name="currency" value="<?php echo $currency;?>">
                <input type="hidden" name="test_mode" value="<?php echo $test_mode;?>">
                <input type="hidden" name="non_3d" value="<?php echo $non_3d;?>">
                <input type="hidden" name="merchant_ok_url" value="<?php echo $merchant_ok_url;?>">
                <input type="hidden" name="merchant_fail_url" value="<?php echo $merchant_fail_url;?>">
                <input type="hidden" name="user_name" value="Paytr Test">
                <input type="hidden" name="user_address" value="TEST TEST TEST">
                <input type="hidden" name="user_phone" value="05555555555">
                <input type="hidden" name="user_basket" value="<?php echo $user_basket; ?>">
                <input type="hidden" name="debug_on" value="1">
                    <input type="hidden" name="client_lang" value="<?php echo $client_lang; ?>">
                <input type="hidden" name="paytr_token" value="<?php echo $token; ?>">
                <input type="hidden" name="non3d_test_failed" value="<?php echo $non3d_test_failed; ?>">
                <input type="hidden" name="installment_count" value="<?php echo $installment_count; ?>">
                <input type="hidden" name="card_type" value="<?php echo $card_type; ?>">
                <input type="submit" value="Submit">
                </form> --}}


                

                <div class="my-4">
                    <hr>
                    <form id="cancelForm" method="POST" action="{{ action('SubscriptionController@cancelInvoice', [
                                'invoice_uid' => $invoice->uid,
                    ]) }}">
                        {{ csrf_field() }}
                        <a href="javascript:;" onclick="$('#cancelForm').submit()">
                            {{ trans('messages.subscription.cancel_now_change_other_plan') }}
                        </a>
                    </form>
                    
                </div>

            </div>
            <div class="col-md-2"></div>
        </div>
        <br />
        <br />
        <br />

        
    </body>
</html>