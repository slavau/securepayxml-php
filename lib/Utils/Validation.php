<?php

namespace SecurePay\XMLAPI\Utils;

use Inacho\CreditCard;

/**
 * A class for all commonly used validation methods.
 *
 * This class provides validation to ensure that all fields can be processed by SecurePay's XML API.
 *
 * Class Validation
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Utils
 */
class Validation
{
    /**
     * Formats an amount to from dollar value to cents
     *
     * @param string $amount The amount
     * @return string The formatted amount
     * @throws \InvalidArgumentException When an invalid amount is provided
     */
    public static function getProperAmount($amount) {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException("Invalid amount");
        }
        $amount = strval($amount);
        if (strpos($amount, "-") !== false) {
            throw new \InvalidArgumentException("Amount cannot be negative");
        }
        if (strpos($amount, ".") !== false) {
            $amount = bcmul($amount, 100);
        }
        return $amount;
    }

    /**
     * Returns the string with only the first 32 characters if it exceeds 32 characters.
     *
     * @param string $accountName The account name
     * @return string The account name with no more than 32 characters
     */
    public static function getProperAccountName($accountName)
    {
        return strlen($accountName) > 32 ? substr($accountName, 0, 32) : $accountName;
    }

    /**
     * @param string $cardHolderName
     * @return string
     */
    public static function getProperCardholderName($cardHolderName)
    {
        return strlen($cardHolderName) > 100 ? substr($cardHolderName, 0, 100) : $cardHolderName;
    }

    /**
     * Returns the string with only the first 100 characters if it exceeds 100 characters.
     *
     * @param string $purchaseOrderNo The purchase order number
     * @return string The purchase order number with no more than 100 characters
     */
    public static function getProperPurchaseOrderNo($purchaseOrderNo)
    {
        return strlen($purchaseOrderNo) > 60 ? substr($purchaseOrderNo, 0, 60) : $purchaseOrderNo;
    }

    /**
     * Converts an expiry month to the proper format.
     * If the month is more than 2 characters, get the last 2 digits.
     * If the month is 1 character, left pad the month with 0 to 2 digits.
     *
     * @param string $expiryMonth The expiry month
     * @return string A 2 digit expiry month
     */
    public static function getProperExiryMonth($expiryMonth) {
        if (strlen($expiryMonth) > 2) {
            $expiryMonth = substr($expiryMonth, -2);
        } else if (strlen($expiryMonth) == 1) {
            $expiryMonth = "0" . $expiryMonth;
        }
        return $expiryMonth;
    }

    /**
     * If the expiry year is 2 digits, convert it to the full 4 digits.
     *
     * @param string $expiryYear The expiry year
     * @return string A full 4 digit expiry year
     */
    public static function getProperExpiryYear($expiryYear) {
        if (strlen($expiryYear) == 2) {
            $expiryYear = substr(date("Y"), 0, 2) . $expiryYear;
        }
        return $expiryYear;
    }

    /**
     * Validates that the cerdit card details are corrrect.
     * This function will only validate the values that are provided.
     *
     * @param string $cardNumber (Optional) The card number
     * @param string $expiryMonth (Optional) The expiry month
     * @param string $expiryYear (Optional) The expiry year
     * @param string $cvv (Optional) The CVV
     * @return bool The validity of the credit card information.
     */
    public static function validateCardDetails($cardNumber, $expiryMonth, $expiryYear, $cvv) {
        $creditCard = null;
        if ($cardNumber != null) {
            $creditCard = CreditCard::validCreditCard($cardNumber);
            if (!$creditCard["valid"]) {
                return false;
            }
        }
        if ($cvv != null) {
            if ($creditCard != null) {
                if (!CreditCard::validCvc($cvv, $creditCard["type"])) { // Credit card number provided but invalid CVV
                    return false;
                }
            } else {
                if (strlen($cvv) != 3 && strlen($cvv) != 4) { // No credit card number and CVV is invalid.
                    return false;
                }
            }
        }
        if ($expiryMonth != null && $expiryYear != null) {
            if (!CreditCard::validDate($expiryYear, $expiryMonth)) {
                return false;
            }
        } else if ($expiryMonth != null) {
            if ($expiryMonth < 1 || $expiryMonth > 12) {
                return false;
            }
        } else if ($expiryYear != null) {
            if (strlen($expiryYear) != 4 || $expiryYear < date("Y")) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks whether the credit card number is in the supported list
     *
     * @param string $creditCardNo The credit card number
     * @return bool If the credit card number is supported
     */
    public static function isSupportedCard($creditCardNo) {
        $creditCard = CreditCard::validCreditCard($creditCardNo);
        return in_array($creditCard["type"], Configurations::getConfig("accepted_cards"));
    }

    /**
     * Checks whether the currency is in the supported list
     *
     * @param string $currency The currency code
     * @return bool If the currency is supported
     */
    public static function isSupportedCurency($currency) {
        return in_array($currency, Configurations::getConfig("supported_currencies"));
    }

    /**
     * Checks whether the bank transaction Id from SecurePay is valid
     *
     * @param string $txnId The bank transaction Id
     * @return bool If the bank transaction Id is valid
     */
    public static function isValidTxnId($txnId) {
        if (strlen($txnId) < 6 || strlen($txnId) > 16) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the preauthorisation Id from SecurePay is valid
     *
     * @param string $preauthId The preauthorisation Id
     * @return bool If the preauthorisation Id is valid
     */
    public static function isValidPreauthId($preauthId) {
        if (strlen($preauthId) != 6) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the BSB number is valid
     *
     * @param string $bsbNumber The BSB number
     * @return bool If the BSB number is valid
     */
    public static function isValidBsbNumber($bsbNumber) {
        if (strlen($bsbNumber) != 6) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the account number is valid
     *
     * @param string $accountNumber The account number
     * @return bool If the account number is valid
     */
    public static function isValidAccountNumber($accountNumber) {
        if (strlen($accountNumber) < 1 || strlen($accountNumber) > 9) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the IP address is a valid IPv4 address and is not internal/reserved.
     *
     * @param string $ipAddress The IP address
     * @return bool If the IP address is valid
     */
    public static function isValidIPAddress($ipAddress) {
        if (strlen($ipAddress) > 15) {
            return false;
        }
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
            filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
            !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) !== false ||
            !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) !== false) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the customer's zip code is valid
     *
     * @param string $zipCode The zip code
     * @return bool If the zip code is valid
     */
    public static function isValidZipCode($zipCode) {
        if (strlen($zipCode) > 30) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the length of the customer's town is valid
     *
     * @param string $town The town
     * @return bool If the customer's town is valid
     */
    public static function isValidTown($town) {
        if (strlen($town) > 60) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the customer's billing country code is valid
     *
     * @param string $billingCountry The billing country code
     * @return bool If the billing country code is valid
     */
    public static function isValidBillingCountry($billingCountry) {
        if (strlen($billingCountry) < 2 || strlen($billingCountry) > 3) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the customer's delivery country code is valid
     *
     * @param string $deliveryCountry The delivery country code
     * @return bool If the delivery country code is valid
     */
    public static function isValidDeliveryCountry($deliveryCountry) {
        if (strlen($deliveryCountry) < 2 || strlen($deliveryCountry) > 3) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the customer's email address is properly formatted
     *
     * @param string $emailAddress The email address
     * @return bool If the customer's email address is valid
     */
    public static function isValidEmailAddress($emailAddress) {
        if (strlen($emailAddress) > 100 || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    /**
     * Checks whether the $dateTime is a future date or not.
     *
     * @param \DateTime $dateTime The datetime of a future date
     * @return bool If the $datetime is a future date.
     */
    public static function isDateInFuture($dateTime) {
        if (!$dateTime instanceof \DateTime) {
            return false;
        }
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $dateTime->setTime(0, 0, 0);
        $diff = $now->diff($dateTime);
        if ($diff->days <= 0 || $now > $dateTime) {
            return false;
        }
        return true;
    }

    /**
     * Returns whether the client Id is valid based on the following:
     * If the client Id is within 1 - 20 characters
     * If the client Id does not include spaces.
     *
     * @param $clientId The client Id to check
     * @return bool Whether the client Id is valid
     */
    public static function isValidClientId($clientId) {
        if (strpos($clientId, " ") !== false || strlen($clientId) > 20 || strlen($clientId) < 1) {
            return false;
        }
        return true;
    }
}