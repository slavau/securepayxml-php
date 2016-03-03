<?php

namespace SecurePay\XMLAPI\Requests\RequestTraits;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Encapsulates all Direct Entry traits.
 *
 * Class DirectEntryTraits
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\RequestTraits
 */
trait DirectEntryTraits
{
    /**
     * @var string Should contain the BSB number of the account.
     */
    protected $bsbNumber;

    /**
     * @var string Should contain the account number that will be charged.
     */
    protected $accountNumber;

    /**
     * @var string Should contain the account name
     */
    protected $accountName;

    /**
     * @var bool Indicates if the direct entry transaction should be debit or credit. (yes - credit, no - debit)
     */
    protected $creditFlag = false;

    /**
     * Retrieves the BSB number
     *
     * @return string The BSB number
     */
    public function getBsbNumber()
    {
        return $this->bsbNumber;
    }

    /**
     * Sets the BSB number
     *
     * @throws \InvalidArgumentException When the BSB number is not 6 digits.
     * @param string $bsbNumber The BSB number for the account that the transaction will be processed against
     * @return $this
     */
    public function setBsbNumber($bsbNumber)
    {
        if (!Validation::isValidBsbNumber($bsbNumber)) {
            throw new \InvalidArgumentException("BSB Number must be 6 digits.");
        }
        $this->bsbNumber = $bsbNumber;
        return $this;
    }

    /**
     * Retrieves the account number
     *
     * @return string The account number.
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Sets the account number
     *
     * @throws \InvalidArgumentException When the account number is NOT between 1 - 9 digits.
     * @param string $accountNumber The account number that the transaction will be processed against
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        if (!Validation::isValidAccountNumber($accountNumber)) {
            throw new \InvalidArgumentException("Account number must be between 1 and 9 digits.");
        }
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * Retrieves the account name
     *
     * @return string The account name
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Sets the account name
     *
     * @param string $accountName The account name that the transaction will be processed against.
     * The account name will be truncated after 32 characters.
     * @return $this
     */
    public function setAccountName($accountName)
    {
        $this->accountName = Validation::getProperAccountName($accountName);
        return $this;
    }

    /**
     * Returns whether the direct entry transaction should be debit or credit
     *
     * @return bool The direct entry credit flag
     */
    public function isCreditFlagEnabled()
    {
        return $this->creditFlag;
    }

    /**
     * Sets the credit flag (true - credit, false - debit)
     *
     * @param boolean $creditFlag The direct entry credit flag
     * @return $this
     */
    public function setCreditFlag($creditFlag)
    {
        $this->creditFlag = $creditFlag;
        return $this;
    }

    /**
     * Generates an array with the DirectEntryInfo object.
     *
     * @return An array with the DirectEntryInfo object
     */
    public function generateDirectEntryInfo() {
        $ret = ["DirectEntryInfo" => []];
        $ret["DirectEntryInfo"][] = ["bsbNumber" => $this->getBsbNumber()];
        $ret["DirectEntryInfo"][] = ["accountNumber" => $this->getAccountNumber()];
        $ret["DirectEntryInfo"][] = ["accountName" => $this->getAccountName()];
        if ($this->getBsbNumber() != null || $this->getAccountNumber() != null || $this->getAccountName() != null)
            $ret["DirectEntryInfo"][] = ["creditFlag" => $this->isCreditFlagEnabled() ? "yes" : "no"];
        return $ret;
    }
}