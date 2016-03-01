<?php

namespace SecurePay\XMLAPI\Requests\Payment\Txn;

use SecurePay\XMLAPI\Exceptions\CurrencyUnsupportedException;
use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * The base class for the payment transactions.
 *
 * Class Txn
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment\Txn
 */
abstract class Txn
{
    /**
     * @var string The recurring flag which indicates that the payment will be ongoing.
     */
    protected $recurringFlag;

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

    /**
     * Txn constructor.
     */
    protected function __construct()
    {
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

    /**
     * Returns the status of whether the recurring flag should be used.
     *
     * @return bool Indicates whether recurring flag should be used.
     */
    public function getRecurringFlag()
    {
        return $this->recurringFlag;
    }

    /**
     * Sets the status of whether to use the recurring flag or not.
     *
     * @param bool $recurringFlag A boolean value to indicate whether the recurring flag should be used.
     * @return $this
     */
    public function setRecurringFlag($recurringFlag)
    {
        $this->recurringFlag = $recurringFlag;
        return $this;
    }

    /**
     * Checks whether all the required transaction fields have been set.
     *
     * @return bool Whether the transaction is ready or not.
     */
    public function isAllRequiredValuesSet() {
        if ($this->getAmount() == null ||
            $this->getCurrency() == null ||
            $this->getPurchaseOrderNo() == null ||
            !$this->txnReady()) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the required transaction fields for the sub-class has been set.
     *
     * @return bool Whether the transaction is ready or not.
     */
    protected abstract function txnReady();

    /**
     * Gets the transaction type of this request.
     *
     * @return string Returns a string value indicating the type of request.
     */
    protected abstract function getTxnType();

    /**
     * Returns the <txn> XML body of the transaction.
     *
     * @return string The XML message request
     */
    public abstract function generateRequestObject();

    /**
     * Returns the SecurePay URL based on the first transaction in the list.
     *
     * @return string The SecurePay URL
     */
    public abstract function getApiUrl();
}