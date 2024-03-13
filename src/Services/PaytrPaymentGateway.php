<?php

namespace Acelle\Paytr\Services;

use Acelle\Library\Contracts\PaymentGatewayInterface;
use Acelle\Library\TransactionResult;
use Acelle\Paytr\Paytr;
use Acelle\Model\Transaction;

class PaytrPaymentGateway implements PaymentGatewayInterface
{
    public $merchant_id;
    public $merchant_key;
    public $merchant_salt;

    public const TYPE = 'paytr';

    /**
     * Construction
     */
    public function __construct($merchant_id, $merchant_key, $merchant_salt)
    {
        $this->merchant_id = $merchant_id;
        $this->merchant_key = $merchant_key;
        $this->merchant_salt = $merchant_salt;
    }

    public function getName() : string
    {
        return 'Paytr';
    }

    public function getType() : string
    {
        return self::TYPE;
    }

    public function getDescription() : string
    {
        return trans('paytr::messages.paytr.description');
    }

    public function getShortDescription() : string
    {
        return trans('paytr::messages.paytr.short_description');
    }

    public function isActive() : bool
    {
        return ($this->merchant_id && $this->merchant_key && $this->merchant_salt);
    }

    public function getSettingsUrl() : string
    {
        return action("\Acelle\Paytr\Controllers\PaytrController@settings");
    }

    public function getCheckoutUrl($invoice) : string
    {
        return action("\Acelle\Paytr\Controllers\PaytrController@checkout", [
            'invoice_uid' => $invoice->uid,
        ]);
    }

    public function allowManualReviewingOfTransaction() : bool
    {
        return false;
    }

    public function autoCharge($invoice)
    {
        $gateway = $this;
        $paytr = Paytr::initialize();

        $invoice->checkout($this, function($invoice) use ($paytr) {
            try {
                // charge invoice
                $paytr->pay($invoice);

                return new TransactionResult(TransactionResult::RESULT_DONE);
            } catch (\Exception $e) {
                return new TransactionResult(TransactionResult::RESULT_FAILED, $e->getMessage() .
                    '. <a href="' . $paytr->gateway->getCheckoutUrl($invoice) . '">Click here</a> to manually charge.');
            }
        });
    }

    public function getAutoBillingDataUpdateUrl($returnUrl='/') : string
    {
        throw new \Exception('
            Paytr gateway does not support auto charge.
            Therefor method getAutoBillingDataUpdateUrl is not supported.
            Something wrong in your design flow!
            Check if a gateway supports auto billing by calling $gateway->supportsAutoBilling().
        ');
    }

    public function supportsAutoBilling() : bool
    {
        return false;
    }

    /**
     * Check if service is valid.
     *
     * @return void
     */
    public function test()
    {
        $paytr = Paytr::initialize();
        $paytr->test();
    }

    public function getMinimumChargeAmount($currency)
    {
        return 0;
    }

    public function verify(Transaction $transaction) : TransactionResult
    {
        $paytr = Paytr::initialize();

        // not match payment service id
        if ($this->publicKey !== $transaction->payment_service_id) {
            throw new \Exception( "Service ID [#{$this->publicKey}] does not match the one in transaction's [#{$transaction->payment_service_id}]");
        }

        return $paytr->verifyCheckout($transaction);
    }
}
