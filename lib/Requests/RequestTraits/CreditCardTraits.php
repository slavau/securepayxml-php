<?php

namespace SecurePay\XMLAPI\Requests\RequestTraits;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Encapsulates Credit card information traits
 *
 * Class CreditCardTraits
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\RequestTraits
 */
trait CreditCardTraits
{
    /**
     * @var string Should be the credit card number to process the request on
     */
    protected $creditCardNo;

    /**
     * @var string Should be the expiry month on the carrd to process the request on
     */
    protected $expiryMonth;

    /**
     * @var string Should be the expiry year on the carrd to process the request on
     */
    protected $expiryYear;

    /**
     * @var string Should be the CVV number on the card to process the request on
     */
    protected $cvv;

    /**
     * @var string Should be the card holder on the card to process the request on
     */
    protected $cardHolderName;

    /**
     * Set any details of the card (number, expiry month, expiry year, cvv)
     *
     * @throws \InvalidArgumentException When the credit card number fails the lunh check alogorithm or is not 15-16 digits.
     * @throws CardTypeUnsupportedException If the credit card number is for a credit card type which is not supported.
     * @param string $creditCardNo (Optional) The card number that the transaction will be processed against
     * @param string $expiryMonth (Optional) The expiry month on the card
     * @param string $expiryYear (Optional) The expiry year on the card
     * @param string $cvv (Optional) The CVV on the card.
     * @return $this
     */
    public function setCardDetails($creditCardNo = null, $expiryMonth = null, $expiryYear = null, $cvv = null)
    {
        if ($expiryMonth != null) {
            $expiryMonth = Validation::getProperExiryMonth($expiryMonth);
        }
        if ($expiryYear != null) {
            $expiryYear = Validation::getProperExpiryYear($expiryYear);
        }
        if (!Validation::validateCardDetails($creditCardNo == null ? $this->creditCardNo : $creditCardNo, $expiryMonth == null ? $this->expiryMonth : $expiryMonth, $expiryYear == null ? $this->expiryYear : $expiryYear, $cvv == null ? $this->cvv : $cvv)) {
            throw new \InvalidArgumentException("Invalid credit card details");
        }
        if ($creditCardNo != null && !Validation::isSupportedCard($creditCardNo)) {
            throw new CardTypeUnsupportedException("Card type is unsupported.");
        }
        if ($creditCardNo != null)
            $this->creditCardNo = $creditCardNo;

        if ($expiryMonth != null)
            $this->expiryMonth = $expiryMonth;

        if ($expiryYear != null)
            $this->expiryYear = $expiryYear;

        if ($cvv != null)
            $this->cvv = $cvv;

        return $this;
    }

    /**
     * Sets the credit card number
     *
     * @throws \InvalidArgumentException When the credit card number fails the lunh check alogorithm or is not 15-16 digits.
     * @throws CardTypeUnsupportedException If the credit card number is for a credit card type which is not supported.
     * @param string $creditCardNo The credit card number for processing
     * @return $this
     */
    public function setCreditCardNo($creditCardNo)
    {
        if (!Validation::validateCardDetails($creditCardNo, $this->expiryMonth, $this->expiryYear, $this->cvv)) {
            throw new \InvalidArgumentException("Invalid credit card number");
        }
        if (!Validation::isSupportedCard($creditCardNo)) {
            throw new CardTypeUnsupportedException("Card type is unsupported.");
        }
        $this->creditCardNo = $creditCardNo;
        return $this;
    }

    /**
     * Sets the expiry month
     *
     * @throws \InvalidArgumentException When the expiry month is not between 1 - 12.
     * @param string $expiryMonth 2 digit expiry month
     * @return $this
     */
    public function setExpiryMonth($expiryMonth)
    {
        $expiryMonth = Validation::getProperExiryMonth($expiryMonth);
        if (!Validation::validateCardDetails($this->creditCardNo, $expiryMonth, $this->expiryYear, $this->cvv)) {
            throw new \InvalidArgumentException("Invalid expiry month");
        }
        $this->expiryMonth = $expiryMonth;
        return $this;
    }

    /**
     * Sets the expiry year
     *
     * @throws \InvalidArgumentException When the expiry year is passed or invalid
     * @param string $expiryYear 2 or 4 digit expiry year/.
     * @return $this
     */
    public function setExpiryYear($expiryYear)
    {
        $expiryYear = Validation::getProperExpiryYear($expiryYear);
        if (!Validation::validateCardDetails($this->creditCardNo, $this->expiryMonth, $expiryYear, $this->cvv)) {
            throw new \InvalidArgumentException("Invalid expiry year");
        }
        $this->expiryYear = $expiryYear;
        return $this;
    }

    /**
     * Returns a formatted expiry date.
     *
     * @return null|string An expiry date with the format mm/yy
     */
    protected function getFormattedExpiryDate()
    {
        if (isset($this->expiryMonth) && isset($this->expiryYear)) {
            return $this->expiryMonth . '/' . substr($this->expiryYear, 2, 2);
        }
        return null;
    }

    /**
     * Sets the CVV number.
     *
     * @throws \InvalidArgumentException If the CVV does not conform with the credit card's type.
     * @param string $cvv The CVV from the back of the credit card.
     * @return $this
     */
    public function setCvv($cvv)
    {
        if (!Validation::validateCardDetails($this->creditCardNo, $this->expiryMonth, $this->expiryYear, $cvv)) {
            throw new \InvalidArgumentException("Invalid CVV number");
        }
        $this->cvv = $cvv;
        return $this;
    }

    /**
     * Returns the card holder's name
     *
     * @return string The card holder's name
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * Sets the card holder's name. Anything after 100 characters will be truncated.
     *
     * @param $cardHolderName The card holder's name
     * @return $this
     */
    public function setCardHolderName($cardHolderName)
    {
        $this->cardHolderName = Validation::getProperCardholderName($cardHolderName);
        return $this;
    }

    /**
     * Generates an array with the CreditCardInfo object.
     *
     * @return array An array with the CreditCardInfo object
     */
    public function generateCreditCardInfo() {
        $ret = ["CreditCardInfo" => []];
        $ret["CreditCardInfo"][] = ["cardNumber" => $this->creditCardNo];
        $ret["CreditCardInfo"][] = ["expiryDate" => $this->getFormattedExpiryDate()];
        if ($this->cvv != null)
            $ret["CreditCardInfo"][] = ["cvv" => $this->cvv];
        return $ret;
    }
}