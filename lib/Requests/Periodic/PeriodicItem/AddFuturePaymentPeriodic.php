<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

use SecurePay\XMLAPI\Exceptions\CurrencyUnsupportedException;
use SecurePay\XMLAPI\Exceptions\InvalidStartDateException;
use SecurePay\XMLAPI\Requests\RequestTraits\CreditCardTraits;
use SecurePay\XMLAPI\Requests\RequestTraits\DirectEntryTraits;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Add a future once off payment
 *
 * Allows you to setup a payment that will only occur once on a specified future date.
 *
 * Class AddFuturePaymentPeriodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
class AddFuturePaymentPeriodic extends Periodic
{
    use CreditCardTraits;
    use DirectEntryTraits;

    /**
     * @var string The amount to process the transaction for in cents value.
     */
    protected $amount;

    /**
     * @var string The currency of the transaction
     */
    protected $currency;

    /**
     * @var \DateTime Should be the date of when the transaction will be processed
     */
    private $startDate;

    public function __construct()
    {
        parent::__construct();
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
            throw new CurrencyUnsupportedException("Unsupported currency: " . $currency);
        }
        $this->currency = $currency;
        return $this;
    }

    /**
     * Gets the date which the transaction will be processed in string form.
     *
     * @return string The processing date
     */
    public function getStartDate()
    {
        if ($this->startDate == null) {
            return null;
        }
        return $this->startDate->format("Ymd");
    }

    /**
     * Sets the date which the transaction will be processed.
     *
     * @param string|\DateTime $startDate The DateTime of when the transaction will be processed.
     * @throws InvalidStartDateException When the start date is not a future date.
     * @return $this
     */
    public function setStartDate($startDate)
    {
        if (is_string($startDate)) {
            try {
                $startDate = new \DateTime($startDate);
            } catch (Exception $e) {
                throw $e;
            }
        }
        if (!Validation::isDateInFuture($startDate)) {
            throw new InvalidStartDateException("Scheduled date is invalid. Must be in the future.");
        }
        $this->startDate = $startDate;
        return $this;
    }

    protected function periodicItemReady()
    {
        if ($this->startDate == null ||
            $this->getAmount() == null) {
            return false;
        }
        $accountTypeToCharge = $this->determineAccountType();
        if ($accountTypeToCharge === parent::ACCOUNT_TYPE_CREDIT_CARD) {
            if ($this->expiryMonth == null ||
                $this->expiryYear == null ||
                $this->creditCardNo == null) {
                return false;
            }
        } else if ($accountTypeToCharge === parent::ACCOUNT_TYPE_DIRECT_ENTRY) {
            if ($this->getAccountName() == null ||
                $this->getBsbNumber() == null ||
                $this->getAccountNumber() == null) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    protected function getPeriodicItemType()
    {
        return "add";
    }

    public function generateRequestObject()
    {
        $txnObj = ["actionType" => $this->getPeriodicItemType(),
            "clientID" => $this->getClientId(),
            "amount" => $this->getAmount(),
            "currency" => $this->getCurrency(),
            "startDate" => $this->getStartDate(),
            "periodicType" => "1"]; // value for scheduling a future payment
        $accountTypeToCharge = $this->determineAccountType();
        if ($accountTypeToCharge === parent::ACCOUNT_TYPE_CREDIT_CARD) {
            $txnObj[] = $this->generateCreditCardInfo();
        } else if ($accountTypeToCharge === parent::ACCOUNT_TYPE_DIRECT_ENTRY) {
            $txnObj[] = $this->generateDirectEntryInfo();
        }
        return $txnObj;
    }
}