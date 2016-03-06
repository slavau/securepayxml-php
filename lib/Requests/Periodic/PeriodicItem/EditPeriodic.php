<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

use SecurePay\XMLAPI\Requests\RequestTraits\CreditCardTraits;
use SecurePay\XMLAPI\Requests\RequestTraits\DirectEntryTraits;

/**
 * The edit function will allow you to edit some of the details against a future payment, payment schedule or Payor.
 *
 * Class EditPeriodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
class EditPeriodic extends Periodic
{
    use CreditCardTraits;
    use DirectEntryTraits;

    /**
     * @var string The payor's reference number.
     */
    private $clientId;

    /**
     * @var bool Should be to determine whether the credit card number should be used (true - credit card, false - direct entry)
     */
    private $useCreditCard;

    public function __construct()
    {
        parent::__construct();
    }

    protected function periodicItemReady()
    {
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
        } else if ($accountTypeToCharge === parent::ACCOUNT_TYPE_UNIDENTIFIED) {
            return false;
        }
        return true;
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

    protected function getPeriodicItemType()
    {
        return "edit";
    }

    public function generateRequestObject()
    {
        $txnObj = ["actionType" => $this->getPeriodicItemType(),
            "clientID" => $this->getClientId(),
            $txnObj[] = $this->generateCreditCardInfo(),
            $txnObj[] = $this->generateDirectEntryInfo()];
        return $txnObj;
    }
}