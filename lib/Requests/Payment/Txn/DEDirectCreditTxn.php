<?php

namespace SecurePay\XMLAPI\Requests\Payment\Txn;

use SecurePay\XMLAPI\Requests\RequestTraits\DirectEntryTraits;
use SecurePay\XMLAPI\Utils\Configurations;

/**
 * Processes a direct credit to a bank account
 *
 * This uses the BSB and account number to send funds to a customer’s bank account.
 * To be eligible to use direct debit, you must have an active direct credit account with SecurePay.
 * Direct entry payments are not processed in real time; they are stored in SecurePay’s database and processed daily at 4.30pm EST.
 *
 * Class DEDirectCreditTxn
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment\Txn
 */
class DEDirectCreditTxn extends Txn
{
    use DirectEntryTraits;

    public function __construct()
    {
        parent::__construct();
        $this->setCurrency(true);
    }

    protected function getTxnType()
    {
        return PaymentRequest::DE_DIRECT_CREDIT;
    }

    public function generateRequestObject()
    {
        $txnObj = ["txnType" => $this->getTxnType(),
            "txnSource" => Configurations::getConfig("default_txn_source"),
            "amount" => $this->getAmount(),
            "currency" => $this->getCurrency(),
            "purchaseOrderNo" => $this->getPurchaseOrderNo(),
            $this->generateDirectEntryInfo()];
        return $txnObj;
    }

    public function getApiUrl()
    {
        return "/xmlapi/directentry";
    }

    protected function txnReady()
    {
        if (!isset($this->bsbNumber) ||
            !isset($this->accountNumber) ||
            !isset($this->accountName)) {
            return false;
        }
        return true;
    }
}