<?php

namespace SecurePay\XMLAPI\Requests\Payment\Txn;

use SecurePay\XMLAPI\Requests\Payment\PaymentRequest;
use SecurePay\XMLAPI\Requests\RequestTraits\CreditCardTraits;
use SecurePay\XMLAPI\Requests\RequestTraits\FraudguardTraits;
use SecurePay\XMLAPI\Utils\Configurations;

/**
 * Processes a payment request against a credit card.
 *
 * Class StandardPaymentTxn
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment\Txn
 */
class StandardPaymentTxn extends Txn
{
    use FraudguardTraits;
    use CreditCardTraits;

    public function __construct()
    {
        parent::__construct();
    }

    protected function getTxnType()
    {
        return PaymentRequest::STANDARD_PAYMENT;
    }

    public function generateRequestObject()
    {
        $txnObj = ["txnType" => $this->getTxnType(),
            "txnSource" => Configurations::getConfig("default_txn_source"),
            "amount" => $this->getAmount(),
            "currency" => $this->getCurrency(),
            "purchaseOrderNo" => $this->getPurchaseOrderNo(),
            "recurring" => $this->getRecurringFlag() ? "yes" : "no",
            $this->generateCreditCardInfo()];
        if ($this->isUsingFraudguard()) {
            $txnObj[] = $this->generateBuyerInfo();
        }
        return $txnObj;
    }

    public function getApiUrl()
    {
        return $this->isUsingFraudguard() ? "/antifraud/payment" : "/xmlapi/payment";
    }

    protected function txnReady()
    {
        if (!isset($this->creditCardNo)) {
            return false;
        }
        if (!$this->getRecurringFlag() && $this->getFormattedExpiryDate() == null) {
            return false;
        }
        return true;
    }
}