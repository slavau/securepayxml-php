<?php

namespace SecurePay\XMLAPI\Requests\RequestTraits;

use SecurePay\XMLAPI\Utils\Validation;

/**
 * Encapsulates the Fraudguard settings for a transaction.
 *
 * This trait is to be used with other classes to provide Fraudguard functionality.
 * FraudGuard is an optional fraud mitigation tool.
 * FraudGuard uses a series of merchant defined rules to screen transactions, these rules need to be configured through the SecurePay merchant login.
 * To use this feature some additional details need to be passed through in the XML message.
 * FraudGuard may incur an additional fee and needs to be activated on your SecurePay account prior to use.
 *
 * Class FraudguardTraits
 * @author Beng Lim <beng.lim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\RequestTraits
 */
trait FraudguardTraits
{
    /**
     * @var bool Indicates whether Fraudguard should be used.
     */
    private $useFraudguard = false;

    /**
     * @var string The first name of the customer (Not validated by SecurePay).
     */
    private $firstName;

    /**
     * @var string The last name of the customer (Not validated by SecurePay).
     */
    private $lastName;

    /**
     * @var string Should contain a valid IPv4 address.
     */
    private $ipAddress;

    /**
     * @var string Should contain the zip code of the customer.
     */
    private $zipCode;

    /**
     * @var string Should contain the billing or delivery town of the customer.
     */
    private $town;

    /**
     * @var string Should contain the billing country of the customer.
     */
    private $billingCountry;

    /**
     * @var string Should contain the delivery country of the customer.
     */
    private $deliveryCountry;

    /**
     * @var string Should contain the email address of the customer.
     */
    private $emailAddress;

    /**
     * Returns the IP Address of the customer.
     *
     * @return string The IP address of the customer
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Sets the IP address of the customer
     *
     * @throws \InvalidArgumentException When the IP address is not an IPv4 address or not publicly accessible
     * @param string $ipAddress The IP address of the customer
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        if (!Validation::isValidIPAddress($ipAddress)) {
            throw new \InvalidArgumentException("IP Address must be IPv4 and publicly accessible.");
        }
        $this->ipAddress = $ipAddress;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the zip code of the customer
     *
     * @return string The zip code of the customer
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Sets the zip code of the customer.
     *
     * @throws \InvalidArgumentException When the zip code is more than 30 digits.
     * @param string $zipCode
     * @return $this
     */
    public function setZipCode($zipCode)
    {
        if (!Validation::isValidZipCode($zipCode)) {
            throw new \InvalidArgumentException("Zip code must be less than 30 digits");
        }
        $this->zipCode = $zipCode;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the town within either the billing or delivery country of the customer.
     *
     * @return string The town within either the billing or delivery country
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Sets the town within either the billing or delivery country of the customer
     *
     * @throws \InvalidArgumentException When the town is more than 60 characters
     * @param string $town The town within either the billing or delivery country
     * @return $this
     */
    public function setTown($town)
    {
        if (!Validation::isValidTown($town)) {
            throw new \InvalidArgumentException("Town must be less than 60 characters");
        }
        $this->town = $town;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the billing country code of the customer.
     *
     * @return string The billing country code
     */
    public function getBillingCountry()
    {
        return $this->billingCountry;
    }

    /**
     * Sets the billing country code of the customer.
     *
     * @throws \InvalidArgumentException When the billing country is not between 2-3 characters.
     * @param string $billingCountry The billing country code (between 2-3 characters).
     * @return $this
     */
    public function setBillingCountry($billingCountry)
    {
        if (!Validation::isValidBillingCountry($billingCountry)) {
            throw new \InvalidArgumentException("Billing country should be between 2 - 3 characters");
        }
        $this->billingCountry = $billingCountry;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the delivery country code of the customer.
     *
     * @return string The delivery country code
     */
    public function getDeliveryCountry()
    {
        return $this->deliveryCountry;
    }

    /**
     * Sets the delivery country code of the customer.
     *
     * @throws \InvalidArgumentException When the delivery country is not between 2-3 characters.
     * @param string $deliveryCountry The delivery country code (between 2-3 characters).
     * @return $this
     */
    public function setDeliveryCountry($deliveryCountry)
    {
        if (!Validation::isValidDeliveryCountry($deliveryCountry)) {
            throw new \InvalidArgumentException("Delivery country should be between 2 - 3 characters");
        }
        $this->deliveryCountry = $deliveryCountry;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the email address of the customer
     *
     * @return string The email address
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Sets the email address of the customer.
     *
     * @throws \InvalidArgumentException
     * @param string $emailAddress The email address
     * @return $this
     */
    public function setEmailAddress($emailAddress)
    {
        if (!Validation::isValidEmailAddress($emailAddress)) {
            throw new \InvalidArgumentException("Email address is invalid");
        }
        $this->emailAddress = $emailAddress;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the first name of the customer
     *
     * @return string The first name
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the first name of the customer.
     *
     * @param string $firstName The first name
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        $this->setUseFraudguard(true);
        return $this;
    }

    /**
     * Returns the last name of the customer
     *
     * @return string The last name
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the last name of the customer.
     *
     * @param string $lastName The last name
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        $this->setUseFraudguard(true);
        return $this;
    }


    /**
     * Returns the status of whether Fraudguard should be used.
     *
     * @return bool Indicates whether Fraudguard should be used.
     */
    public function isUsingFraudguard()
    {
        return $this->useFraudguard;
    }

    /**
     * Sets the status of whether to use Fraudguard or not.
     *
     * @param bool $useFraudguard A boolean value to indicate whether Fraudguard should be used.
     * @return $this
     */
    public function setUseFraudguard($useFraudguard)
    {
        $this->useFraudguard = $useFraudguard;
        return $this;
    }

    /**
     * Generates an array with the BuyerInfo object which is used for Fraudguard.
     * Any values that are not set will not be included in the Fruadguard message.
     *
     * @return array An array with the BuyerInfo object
     */
    public function generateBuyerInfo() {
        $ret = ["BuyerInfo" => []];
        if ($this->getFirstName() != null) {
            $ret["BuyerInfo"][] = ["firstName" => $this->getFirstName()];
        }
        if ($this->getLastName() != null) {
            $ret["BuyerInfo"][] = ["lastName" => $this->getLastName()];
        }
        if ($this->getZipCode() != null) {
            $ret["BuyerInfo"][] = ["zipCode" => $this->getZipCode()];
        }
        if ($this->getTown() != null) {
            $ret["BuyerInfo"][] = ["town" => $this->getTown()];
        }
        if ($this->getBillingCountry() != null) {
            $ret["BuyerInfo"][] = ["billingCountry" => $this->getBillingCountry()];
        }
        if ($this->getDeliveryCountry() != null) {
            $ret["BuyerInfo"][] = ["deliveryCountry" => $this->getDeliveryCountry()];
        }
        if ($this->getEmailAddress() != null) {
            $ret["BuyerInfo"][] = ["emailAddress" => $this->getEmailAddress()];
        }
        if ($this->getIpAddress() != null) {
            $ret["BuyerInfo"][] = ["ip" => $this->getIpAddress()];
        }
        return $ret;
    }
}