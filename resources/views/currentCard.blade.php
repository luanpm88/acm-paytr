@if ($invoice->customer->getAutoBillingData())
<?php

    $merchant_id = $paytr->gateway->merchant_id;
    $merchant_key = $paytr->gateway->merchant_key;
    $merchant_salt = $paytr->gateway->merchant_salt;

    $utoken = $invoice->customer->getAutoBillingData()->getData()['utoken'];

    $hash_str = $utoken . $merchant_salt;
    $paytr_token=base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));
    $post_vals=array(
        'merchant_id'=>$merchant_id,
        'utoken'=>$utoken,
        'paytr_token'=>$paytr_token
    );

    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/capi/list");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1) ;
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $result = @curl_exec($ch);

    if(curl_errno($ch))
        die("PAYTR CAPI List connection error. err:".curl_error($ch));

    curl_close($ch);

    $result=json_decode($result,1);

    // var_dump($result);die();

    // if($result['status']=='error')
    //     die("PAYTR CAPI list failed. Error:".$result['err_msg']);
    // else
    //     print_r($result);

    //     die();

    if (count($result)) {
        $card = $result[0];
?>

<h4 class="fw-600 mb-3 mt-0">Current Card</h4>
    <ul class="dotted-list topborder section">
        <li>
            <div class="unit size1of2">
                <strong>Card type</strong>
            </div>
            <div class="lastUnit size1of2">
                <mc:flag><strong>{{ $card['c_type'] }}</strong></mc:flag>
            </div>
        </li>
        <li class="selfclear">
            <div class="unit size1of2">
                <strong>Last 4 number</strong>
            </div>
            <div class="lastUnit size1of2">
                <mc:flag><strong>{{ $card['last_4'] }}</strong></mc:flag>
            </div>
        </li>
    </ul>


    

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

    $card_type = "axess"; // Sent only in installments. Avaliable values; advantage, axess, bonus, cardfinans, maximum, paraf, world
    $installment_count = "5"; // 2-12 sent only in installments

    $post_url = "https://www.paytr.com/odeme";

    $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $payment_type . $installment_count . $currency . $test_mode . $non_3d;
    $token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

    ## Usage of require_cvv, utoken and ctoken values returned from CAPI LIST service ##
    ## The registered card list of the user who made the payment is taken and listed in front of the user. ##
    ## User selects the card to pay from among the listed cards ##
    ## The ctoken information of the card chosen by the user and the utoken information of the user are sent in the payment request. ##
    $utoken = $invoice->customer->getAutoBillingData()->getData()['utoken'];
    $ctoken = $card['ctoken'];
    

    $user_name = $invoice->billing_first_name . ' ' . $invoice->billing_last_name;
    $user_address = $invoice->billing_address;
    $user_phone = $invoice->billing_phone;

    $post_vals=array(
        'merchant_id'=>$merchant_id,
        'user_ip'=>$user_ip,
        'merchant_oid'=>$merchant_oid,
        'email'=>$email,
        'payment_type'=>$payment_type,
        'payment_amount'=>$payment_amount,
        'installment_count'=>"0",
        'currency'=>$currency,
        'test_mode'=>$test_mode,
        'non_3d'=>$non_3d,
        'merchant_ok_url'=>$merchant_ok_url,
        'merchant_fail_url'=>$merchant_fail_url,
        'user_name'=>$user_name,
        'user_address'=>$user_address,
        'user_phone'=>$user_phone,
        'user_basket'=>$user_basket,
        'debug_on'=>1,
        'paytr_token'=>$token,
        'non3d_test_failed'=>$non3d_test_failed,
        'installment_count'=>$installment_count,
        'card_type'=>$card_type,
        'utoken'=>$utoken,
        'ctoken'=>$ctoken,
    );

    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1) ;
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $result = @curl_exec($ch);

    if(curl_errno($ch))
        die("PAYTR CAPI List connection error. err:".curl_error($ch));

    curl_close($ch);

    // $result=json_decode($result,1);

    var_dump($result);die();

    ?>

    <!-- <form action="<?php echo $post_url; ?>" method="post">
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
        <input type="hidden" name="ctoken" value="<?php echo $ctoken; ?>">
        <input type="submit" class="btn btn-primary mt-3 mb-4" value="Pay with this card">
    </form> -->

    <?php } ?>

@endif