<?php

namespace Acelle\Paytr\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller as BaseController;
use Acelle\Model\Invoice;
use Acelle\Paytr\Paytr;
use Acelle\Library\Facades\Billing;
use Acelle\Library\TransactionResult;
use Acelle\Library\AutoBillingData;

class PaytrController extends BaseController
{
    public function settings(Request $request)
    {
        $paytr = Paytr::initialize();      

        if ($request->isMethod('post')) {
            // save Paytr setting
            $validator = $paytr->saveAPISettings($request->all());

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('paytr::settings', [
                    'paytr' => $paytr,
                    'errors' => $validator->errors(),
                ], 400);
            }

            if ($request->enable_gateway) {
                $paytr->plugin->activate();
                Billing::enablePaymentGateway($paytr->gateway->getType());
            }

            if ($paytr->plugin->isActive()) {
                return redirect()->action("Admin\PaymentController@index")
                    ->with('alert-success', trans('cashier::messages.gateway.updated'));
            } else {
                return redirect()->action("Admin\PluginController@index")
                    ->with('alert-success', trans('cashier::messages.gateway.updated'));
            }
        }

        return view('paytr::settings', [
            'paytr' => $paytr,
        ]);
    }

    public function checkout(Request $request, $invoice_uid)
    {        
        $invoice = Invoice::findByUid($invoice_uid);
        $paytr = Paytr::initialize($invoice);
        
        // Save return url
        if ($request->return_url) {
            $request->session()->put('checkout_return_url', $request->return_url);
        }

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }

        // use old card
        if ($request->isMethod('post')) {
            
        }

        return view('paytr::checkout', [
            'paytr' => $paytr,
            'invoice' => $invoice,
        ]);
    }

    public function success(Request $request, $invoice_uid)
    {
        return redirect()->away(Billing::getReturnUrl());
    }

    public function notification(Request $request)
    {
        $invoice = Invoice::findByUid(explode('0000', $request->merchant_oid)[1]);
        $customer = $invoice->customer;

        try {
            $paytr = Paytr::initialize($invoice);

            $post = $_POST;

            $merchant_key   = $paytr->gateway->merchant_key;
            $merchant_salt  = $paytr->gateway->merchant_salt;

            $hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );

            if( $hash != $post['hash'] )
                throw new \Exception('PAYTR notification failed: bad hash');

            if( $post['status'] == 'success' ) {

                if (isset($post['utoken'])) {
                    // save billing data
                    $autoBillingData = new AutoBillingData($paytr->gateway, $post);
                    $customer->setAutoBillingData($autoBillingData);
                }
                    

                $invoice->checkout($paytr->gateway, function($invoice) {
                    return new TransactionResult(TransactionResult::RESULT_DONE);
                });
            } else { 
                throw new \Exception('Pay error: ' . json_encode($post));
            }
        } catch (\Exception $e) {
            $invoice->checkout($paytr->gateway, function($invoice) use ($e) {
                return new TransactionResult(TransactionResult::RESULT_FAILED, $e->getMessage());
            });
        }
    }

    public function failed(Request $request, $invoice_uid)
    {
        $invoice = Invoice::findByUid($invoice_uid);
        $paytr = Paytr::initialize($invoice);

        var_dump($request->all());
    }
}
