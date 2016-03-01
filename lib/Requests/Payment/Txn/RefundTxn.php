<?php

namespace SecurePay\XMLAPI\Requests\Payment\Txn;

use SecurePay\XMLAPI\Requests\Payment\PaymentRequest;
use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Processes a refund against a previous transaction.
 *
 * Processes a refund against a previous transaction through the SecurePay system back to the original credit card.
 * Transactions may only be refunded up to the original amount processed. Multiple partial refunds are possible.
 *
 * Class RefundTxn
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment\Txn
 */
class RefundTxn extends Txn
{
    /**
     * @var string Should contain the original bank transaction Id for refund.
     */
    private $txnId;

    public function __construct($txnId = null)
    {
        parent::__construct();
        $this->txnId = $txnId;
    }

    protected function getTxnType()
    {
        return PaymentRequest::REFUND;
    }

    protected function txnReady()
    {
        if (!isset($this->txnId)) {
            return false;
        }
        return true;
    }

    public function generateRequestObject()
    {
        $txnObj = ["txnType" => $this->getTxnType(),
            "txnSource" => Configurations::getConfig("default_txn_source"),
            "amount" => $this->getAmount(),
            "currency" => $this->getCurrency(),
            "purchaseOrderNo" => $this->getPurchaseOrderNo(),
            "txnID" => $this->getTxnId()
        ];

        return $txnObj;
    }

    /**
     * Returns the bank transaction Id which should match with the bank transaction Id of the original transaction to refund.
     *
     * @return string The bank transaction Id
     */
    public function getTxnId()
    {
        return $this->txnId;
    }

    /**
     * Sets the bank transaction Id
     *
     * @throws \InvalidArgumentException When the transaction Id is not between 6-16 digits.
     * @param string $txnId
     * @return $this
     */
    public function setTxnId($txnId)
    {
        if (!Validation::isValidTxnId($txnId)) {
            throw new \InvalidArgumentException("Invalid transaction ID");
        }
        $this->txnId = $txnId;
        return $this;
    }

    public function getApiUrl()
    {
        return "/xmlapi/payment";
    }


}