<?php

namespace Acelle\Paytr;

use Acelle\Model\Setting;
use Acelle\Model\Plugin;
use Acelle\Library\TransactionResult;
use Acelle\Paytr\Services\PaytrPaymentGateway;
use Acelle\Library\AutoBillingData;

class Paytr
{
    public const NAME = 'acelle/paytr';
    public const GATEWAY = 'paytr';

    public $gateway;
    public $plugin;

    public function __construct()
    {
        $merchant_id = Setting::get('cashier.paytr.merchant_id');
        $merchant_key = Setting::get('cashier.paytr.merchant_key');
        $merchant_salt = Setting::get('cashier.paytr.merchant_salt');

        $this->gateway = new PaytrPaymentGateway($merchant_id, $merchant_key, $merchant_salt);
        $this->plugin = Plugin::where('name', self::NAME)->first();
    }

    public static function initialize()
    {
        return (new self());
    }

    /**
     * Request PayPal service.
     *
     * @return void
     */
    private function request($uri, $type = 'GET', $options = [])
    {
        $client = new \GuzzleHttp\Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->gateway->secretKey,
        ];

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
        }

        $response = $client->request($type, $uri, [
            'headers' => $headers,
            'form_params' => isset($options['form_params']) ? $options['form_params'] : [],
            'body' => isset($options['body']) ? (is_array($options['body']) ? json_encode($options['body']) : $options['body']) : '',
        ]);

        return json_decode($response->getBody(), true);
    }

    public function saveAPISettings($params)
    {
        $paytr = $this;

        // make validator
        $validator = \Validator::make($params, [
            'merchant_id' => 'required',
            'merchant_key' => 'required',
            'merchant_salt' => 'required',
        ]);

        $paytr->merchant_id = isset($params['merchant_id']) ? $params['merchant_id'] : null;
        $paytr->merchant_key = isset($params['merchant_key']) ? $params['merchant_key'] : null;
        $paytr->merchant_salt = isset($params['merchant_salt']) ? $params['merchant_salt'] : null;

        // test service
        $validator->after(function ($validator) use ($params, $paytr) {
            try {
                // $paytr->test();
            } catch(\Exception $e) {
                $validator->errors()->add('field', 'Can not connect to ' . $paytr->gateway->getName() . '. Error: ' . $e->getMessage());
            }
        });

        // redirect if fails
        if ($validator->fails()) {
            return $validator;
        }

        // save settings
        Setting::set('cashier.paytr.merchant_id', $params['merchant_id']);
        Setting::set('cashier.paytr.merchant_key', $params['merchant_key']);
        Setting::set('cashier.paytr.merchant_salt', $params['merchant_salt']);

        return $validator;
    }

    public function test()
    {
        throw new \Exception('test() function not emplement yet!');
    }

    public function verifyCheckout($transaction) : TransactionResult
    {
        try {
            $result = $this->request('https://api.paytr.com/v3/transactions/'.$transaction->checkout_id.'/verify', 'GET');
        } catch (\Exception $e) {
            return new TransactionResult(
                TransactionResult::RESULT_FAILED,
                'Paytr remote transaction is failed with error data: ' . $e->getMessage()
            );
        }

        if (!$result || !isset($result['status']) || $result['status'] != 'success') {
            return new TransactionResult(
                TransactionResult::RESULT_FAILED,
                'Paytr remote transaction is failed with error data: ' . json_encode($result)
            );
        } else {
            $this->updateCard($transaction->invoice, $result);            
            return new TransactionResult(TransactionResult::RESULT_DONE);
        }
    }

    public function updateCard($invoice, $cardInfo)
    {
        // update auto billing data
        $autoBillingData = new AutoBillingData($this->gateway, $cardInfo);
        $invoice->customer->setAutoBillingData($autoBillingData);
    }

    public function getCard($invoice)
    {
        $autoBillingData = $invoice->customer->getAutoBillingData();        

        if ($autoBillingData == null) {
            return false;
        }

        $metadata = $autoBillingData->getData();

        // check last transaction
        if (!isset($metadata['data']) ||
            !isset($metadata['data']['card'])
        ) {
            return false;
        }

        // card info
        return [
            'token' => $metadata['data']['card']['token'],
            'type' => $metadata['data']['card']['type'],
            'last4' => $metadata['data']['card']['last_4digits'],
        ];
    }

    public function pay($invoice)
    {
        $card = $this->getCard($invoice);

        if (!$card || !isset($card['token'])) {
            throw new \Exception('Customer dose not have card information!');
        }

        $this->request('https://api.paytr.com/v3/tokenized-charges', 'POST', [
            'form_params' => [
                "token" => $card['token'],
                "currency" => $invoice->currency->code,
                "amount" => $invoice->total(),
                "tx_ref" => "tokenized-c-" . now()->timestamp,
            ]
        ]);
    }

    public function saveCheckoutInfoToTransaction($transaction, $publicKey, $checkoutId) {
        $transaction->payment_service_id = $publicKey;
        $transaction->checkout_id = $checkoutId;
        $transaction->save();
    }
}
