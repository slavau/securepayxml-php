<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

use SecurePay\XMLAPI\Exceptions\CurrencyUnsupportedException;
use SecurePay\XMLAPI\Exceptions\InvalidStartDateException;
use SecurePay\XMLAPI\Requests\RequestTraits\CreditCardTraits;
use SecurePay\XMLAPI\Requests\RequestTraits\DirectEntryTraits;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Add an ongoing payment on SecurePay
 *
 * Allows you to setup a payment that will process at a given interval
 *
 * Class AddOngoingPaymentPeriodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
class AddOngoingPaymentPeriodic extends Periodic
{
    use CreditCardTraits;
    use DirectEntryTraits;

    const CALENDAR_PAYMENT_INTERVAL_WEEKLY = "1";
    const CALENDAR_PAYMENT_INTERVAL_FORTNIGHTLY = "2";
    const CALENDAR_PAYMENT_INTERVAL_MONTHLY = "3";
    const CALENDAR_PAYMENT_INTERVAL_QUARTERLY = "4";
    const CALENDAR_PAYMENT_INTERVAL_HALFYEARLY = "5";
    const CALENDAR_PAYMENT_INTERVAL_ANUALLY = "6";

    /**
     * @var string The amount to process the transaction for in cents value.
     */
    protected $amount;

    /**
     * @var string The currency of the transaction
     */
    protected $currency;

    /**
     * @var bool Should be to determine whether the credit card number should be used (true - credit card, false - direct entry)
     */
    private $useCreditCard;

    /**
     * @var \DateTime Should be the date of when the transaction will be processed
     */
    private $startDate;

    /**
     * @var int The days between processing a particular scheduled transaction
     */
    private $dayIntervals;

    /**
     * @var string The interval code. Can be found in const section CALENDA_PAYMENT_INTERRVAL_*
     */
    private $scheduledInterval;

    /**
     * @var int The number of payments expected to process.
     */
    private $numberOfPayments;

    public function __construct()
    {
        parent::__construct();
        $this->setUseCreditCard(true);
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
     * Returns whether we should use credit card details or direct entry details. (true - credit card, false - direct entry)
     *
     * @return boolean Use credit card
     */
    public function isUseCreditCard()
    {
        return $this->useCreditCard;
    }

    /**
     * Sets whether we should use the credit card details or direct entry details. (true - credit card, false - direct entry)
     *
     * @param boolean $useCreditCard Use credit card
     * @return $this
     */
    public function setUseCreditCard($useCreditCard)
    {
        $this->useCreditCard = $useCreditCard;
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

    /**
     * The number of days between when a transaction is processed and the next scheduled day.
     *
     * @return int Number of days
     */
    public function getDayIntervals()
    {
        return $this->dayIntervals;
    }

    /**
     * Sets the number of days between when a transaction is processed and the next scheduled day.
     *
     * @throws \InvalidArgumentException When the day intervals is less than 1
     * @param int $dayIntervals The number of days
     * @return $this
     */
    public function setDayIntervals($dayIntervals)
    {
        if ($dayIntervals < 1) {
            throw new \InvalidArgumentException("Day intervals must be at least 1");
        }
        $this->scheduledInterval = null;
        $this->dayIntervals = $dayIntervals;
        return $this;
    }

    /**
     * Returns the scheduled interval code.
     *
     * @return string Scheduled interval code.
     */
    public function getScheduledInterval()
    {
        return $this->scheduledInterval;
    }

    /**
     * Sets the scheduled interval to one of the following:
     * CALENDAR_PAYMENT_INTERVAL_WEEKLY = "1"
     * CALENDAR_PAYMENT_INTERVAL_FORTNIGHTLY = "2"
     * CALENDAR_PAYMENT_INTERVAL_MONTHLY = "3"
     * CALENDAR_PAYMENT_INTERVAL_QUARTERLY = "4"
     * CALENDAR_PAYMENT_INTERVAL_HALFYEARLY = "5"
     * CALENDAR_PAYMENT_INTERVAL_ANUALLY = "6"
     *
     * @param string $scheduledInterval The scheduled interval code
     * @return $this
     */
    public function setScheduledInterval($scheduledInterval)
    {
        $this->dayIntervals = null;
        $this->scheduledInterval = $scheduledInterval;
        return $this;
    }

    /**
     * Returns the number of payments expected to process
     *
     * @return int The number of payments
     */
    public function getNumberOfPayments()
    {
        return $this->numberOfPayments;
    }

    /**
     * Sets the number of payments expected to process.
     *
     * @throws \InvalidArgumentException When the number of payments is less than 1
     * @param int $numberOfPayments Number of payments
     * @return $this
     */
    public function setNumberOfPayments($numberOfPayments)
    {
        if ($numberOfPayments < 1) {
            throw new \InvalidArgumentException("Number of payments must be at least 1");
        }
        $this->numberOfPayments = $numberOfPayments;
        return $this;
    }

    protected function periodicItemReady()
    {
        if ($this->startDate == null ||
            $this->getAmount() == null ||
            ($this->getDayIntervals() == null && $this->getScheduledInterval() == null) ||
            $this->getNumberOfPayments() == null) {
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

    public function getPeriodicItemType()
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
            "numberOfPayments" => $this->getNumberOfPayments()];
        if ($this->getDayIntervals() != null) {
            $txnObj[] = ["periodicType" => "2"];
            $txnObj[] = ["paymentInterval" => $this->getDayIntervals()];
        } else {
            $txnObj[] = ["periodicType" => "3"];
            $txnObj[] = ["paymentInterval" => $this->getScheduledInterval()];
        }
        $accountTypeToCharge = $this->determineAccountType();
        if ($accountTypeToCharge === parent::ACCOUNT_TYPE_CREDIT_CARD) {
            $txnObj[] = $this->generateCreditCardInfo();
        } else if ($accountTypeToCharge === parent::ACCOUNT_TYPE_DIRECT_ENTRY) {
            $txnObj[] = $this->generateDirectEntryInfo();
        }
        return $txnObj;
    }
}