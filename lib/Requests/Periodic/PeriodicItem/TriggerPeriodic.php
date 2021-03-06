<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * This message will trigger a payment against an existing Payor.
 *
 * Class TriggerPeriodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
class TriggerPeriodic extends Periodic
{
    /**
     * @var string The amount to process the transaction for in cents value.
     */
    protected $amount;

    /**
     * @var string The currency of the transaction
     */
    protected $currency;

    /**
     * @var string The purchase order number which will be recorded against the transaction.
     */
    protected $purchaseOrderNo;

    public function __construct()
    {
        parent::__construct();
        $this->setCurrency(Configurations::getConfig("default_currency"));
    }

    /**
     * Returns The amount to process the transaction for in cents value.
     *
     * @return string the transaction amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the amount to process the transaction for.
     *
     * @throws \InvalidArgumentException When the amount is invalid.
     * @param string $amount The transaction amount
     * @return $this
     */
    public function setAmount($amount)
    {
        try {
            $this->amount = Validation::getProperAmount($amount);
            return $this;
        } catch (\InvalidArgumentException $iae) {
            throw $iae; // rethrow the exception to handle further up in the stack.
        }
    }

    /**
     * Returns the currency of the transaction.
     *
     * @return string The currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     *  Sets the currency to a supported currency.
     *
     * @throws CurrencyUnsupportedException If the currency is not listed as a supported currency
     * @param string $currency The currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        if (!Validation::isSupportedCurency($currency)) {
            throw new CurrencyUnsupportedException("Unsupported currency: ".$currency);
        }
        $this->currency = $currency;
        return $this;
    }

    /**
     * Returns the purchase order number that will be recorded against a transaction.
     *
     * @return string The purchase order number
     */
    public function getPurchaseOrderNo()
    {
        return $this->purchaseOrderNo;
    }

    /**
     * Sets the purchase order number for a transaction. Will truncate anything after 60 characters.
     *
     * @param string $purchaseOrderNo The purchase order number
     * @return $this
     */
    public function setPurchaseOrderNo($purchaseOrderNo)
    {
        $this->purchaseOrderNo = Validation::getProperPurchaseOrderNo($purchaseOrderNo);
        return $this;
    }

    protected function periodicItemReady()
    {
        if ($this->getAmount() == null ||
            $this->getPurchaseOrderNo() == null) {
            return false;
        }
        return true;
    }

    protected function getPeriodicItemType()
    {
        return "trigger";
    }

    public function generateRequestObject()
    {
        $txnObj = ["actionType" => $this->getPeriodicItemType(),
                    "clientID" => $this->getClientId(),
                    "currency" => $this->getCurrency(),
                    "amount" => $this->getAmount(),
                    "transactionReference" => $this->getPurchaseOrderNo()];

        return $txnObj;
    }
}