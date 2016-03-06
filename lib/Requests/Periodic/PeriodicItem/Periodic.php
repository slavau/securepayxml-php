<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

/**
 * The base class for the periodic item.
 *
 * Class Periodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
abstract class Periodic
{
    const ACCOUNT_TYPE_UNIDENTIFIED = 0;
    const ACCOUNT_TYPE_CREDIT_CARD = 1;
    const ACCOUNT_TYPE_DIRECT_ENTRY = 2;
    const ACCOUNT_TYPE_BOTH = 3;

    /**
     * @var string The payor's reference number.
     */
    private $clientId;

    /**
     * Periodic constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Returns the payor's client Id which is used as a reference to trigger a payment
     *
     * @return string The payor's client Id
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets the payor's client Id
     *
     * @param $clientId The payor's client Id
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Checks whether all the required transaction fields have been set.
     *
     * @return bool Whether the transaction is ready or not.
     */
    public function isAllRequiredValuesSet() {
        if ($this->getClientId() == null ||
            !$this->periodicItemReady()) {
            return false;
        }
        return true;
    }

    /**
     * Determines whether the transaction should be processed on a credit card or a direct debit account.
     *
     *
     * @return int The account type that should be charged.
     */
    protected function determineAccountType() {
        $ccFields = 0;
        $deFields = 0;

        if ($this->creditCardNo != null) {
            $ccFields++;
        }
        if ($this->expiryMonth != null) {
            $ccFields++;
        }
        if ($this->expiryYear != null) {
            $ccFields++;
        }

        if ($this->getAccountName() != null) {
            $deFields++;
        }
        if ($this->getAccountNumber() != null) {
            $deFields++;
        }
        if ($this->getBsbNumber() != null) {
            $deFields++;
        }

        if ($ccFields == 3 && $deFields == 3) {
            return self::ACCOUNT_TYPE_BOTH;
        } else if ($ccFields == 3) {
            return self::ACCOUNT_TYPE_CREDIT_CARD;
        } else if ($deFields == 3) {
            return self::ACCOUNT_TYPE_DIRECT_ENTRY;
        } else {
            return self::ACCOUNT_TYPE_UNIDENTIFIED;
        }
    }

    /**
     * Checks whether the required periodic fields for the sub-class has been set.
     *
     * @return bool Whether the periodic item is ready or not.
     */
    protected abstract function periodicItemReady();

    /**
     * Gets the periodic type of this request.
     *
     * @return string Returns a string value indicating the type of request.
     */
    protected abstract function getPeriodicItemType();

    /**
     * Returns the <periodicItem> XML body of the periodic item.
     *
     * @return string The XML message request
     */
    public abstract function generateRequestObject();
}