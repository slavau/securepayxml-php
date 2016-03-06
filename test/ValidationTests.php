<?php

require __DIR__ . '/../vendor/autoload.php';

use SecurePay\XMLAPI\Utils\Validation;

/**
 * Class ValidationTests
 * @author Beng Lim <benglim92@gmail.com>
 */
class ValidationTests extends PHPUnit_Framework_TestCase
{

    public function testSuccessfulGetProperAmount() {
        $pass1 = ["test" => "1.00",
                "expected" => "100"];
        $pass2 = ["test" => "12.5",
                "expected" => "1250"];
        $this->assertSame($pass1["expected"], Validation::getProperAmount($pass1["test"]), 'Testing $'.$pass1["test"], "A valid amount string should pass");
        $this->assertSame($pass2["expected"], Validation::getProperAmount($pass2["test"]), 'Testing $'.$pass2["test"], "A valid amount string should pass");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnsuccessfulGetProperAmount() {
        $testValue = "Random String";
        Validation::getProperAmount($testValue);
    }

    public function testValidCreditCard() {
        $creditCard = [
            "number" => "4444333322221111",
            "expiryMonth" => date("m"), // current month
            "expiryYear" => date("Y"), // current year
            "cvv" => "123"
        ];
        $this->assertTrue(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'Valid credit card details should pass');
    }

    public function testInvalidCreditCardNumber() {
        $creditCard = [
            "number" => "1234123412341234",
            "expiryMonth" => null,
            "expiryYear" => null,
            "cvv" => null,
        ];
        $this->assertFalse(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'An invalid credit card number should fail');
    }

    public function testInvalidCreditCardExpiryMonth() {
        $creditCard = [
            "number" => null,
            "expiryMonth" => "13",
            "expiryYear" => null, // current year
            "cvv" => null,
        ];
        $this->assertFalse(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'An expiry month that is not between 01 and 12 should fail');
    }

    public function testInvalidCreditCardExpiryYear() {
        $creditCard = [
            "number" => null,
            "expiryMonth" => null,
            "expiryYear" => date("Y") - 1,
            "cvv" => null,
        ];
        $this->assertFalse(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'A past expiry year should fail');
    }

    public function testValidCreditCardCVV() {
        $creditCard = [
            "number" => null,
            "expiryMonth" => null,
            "expiryYear" => null,
            "cvv" => "123",
        ];
        $this->assertTrue(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), '3 digit CVV should pass');
    }

    public function testInvalidCreditCardCVV() {
        $creditCard = [
            "number" => null,
            "expiryMonth" => null,
            "expiryYear" => null,
            "cvv" => "12",
        ];
        $this->assertFalse(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'CVV number of 2 digits should fail');

        $creditCard = [
            "number" => null,
            "expiryMonth" => null,
            "expiryYear" => null,
            "cvv" => "12531",
        ];
        $this->assertFalse(Validation::validateCardDetails($creditCard["number"], $creditCard["expiryMonth"], $creditCard["expiryYear"], $creditCard["cvv"]), 'CVV number of 5 digits should fail.');
    }

    public function testGetProperCardholderName() {
        $validName = "My Name";
        $longName = "";
        for($i = 0; $i < 150; $i++) {
            $longName .= ".";
        }
        $shortName = substr($longName, 0, 100);
        $this->assertSame(Validation::getProperCardholderName($validName), $validName, "Name which is within 1 - 100 characters should return same value");
        $this->assertSame(Validation::getProperCardholderName($longName), $shortName, "Name which is longer than 100 should only return 100 characters");
    }

    public function testGetProperPurchaseOrderNo() {
        $validName = "Order #1";
        $longName = "";
        for($i = 0; $i < 150; $i++) {
            $longName .= ".";
        }
        $shortName = substr($longName, 0, 60);
        $this->assertSame(Validation::getProperPurchaseOrderNo($validName), $validName, "Purchase order number which is within 1 - 60 characters should return same value");
        $this->assertSame(Validation::getProperPurchaseOrderNo($longName), $shortName, "Purchase order number which is longer than 60 should only return 60 characters");
    }

    public function testIsValidTxnId() {
        $validTxnId = "12354543";
        $invalidTxnId1 = "11111"; // 5 digits
        $invalidTxnId2 = "11111111111111111"; // 17 digits

        $this->assertTrue(Validation::isValidTxnId($validTxnId), "A transaction ID between 6 and 16 digits should pass");
        $this->assertFalse(Validation::isValidTxnId($invalidTxnId1), "A transaction ID between 6 and 16 digits should fail");
        $this->assertFalse(Validation::isValidTxnId($invalidTxnId2), "A transaction ID between 6 and 16 digits should fail");
    }

    public function testIsValidPreauthId() {
        $validPreauthId = "134536";
        $invalidPreauthId1 = "11111"; // 5 digits
        $invalidPreauthId2 = "11111111111111111"; // 17 digits

        $this->assertTrue(Validation::isValidPreauthId($validPreauthId), "A preauth ID of 6 should pass");
        $this->assertFalse(Validation::isValidPreauthId($invalidPreauthId1), "A preauth ID which is not 6 characters should fail");
        $this->assertFalse(Validation::isValidPreauthId($invalidPreauthId2), "A preauth ID which is not 6 characters should fail");
    }

    public function testGetProperAccountName() {
        $validName = "My Name";
        $longName = "";
        for($i = 0; $i < 150; $i++) {
            $longName .= ".";
        }
        $shortName = substr($longName, 0, 32);
        $this->assertSame(Validation::getProperAccountName($validName), $validName, "Name which is within 1 - 32 characters should return same value");
        $this->assertSame(Validation::getProperAccountName($longName), $shortName, "Name which is longer than 32 should only return 32 characters");
    }

    public function testIsValidBsbNumber() {
        $validBsbNumber = "425467";
        $invalidBsbNumber1 = "11111"; // 5 digits
        $invalidBsbNumber2 = "11111111111111111"; // 17 digits

        $this->assertTrue(Validation::isValidBsbNumber($validBsbNumber), "A BSB number ID of 6 digits should pass");
        $this->assertFalse(Validation::isValidBsbNumber($invalidBsbNumber1), "A BSB number ID which is not 6 digits should fail");
        $this->assertFalse(Validation::isValidBsbNumber($invalidBsbNumber2), "A BSB number ID which is not 6 digits should fail");
    }

    public function testIsValidAccountNumber() {
        $validAccountNumber = "12354543";
        $invalidAccountNumber1 = ""; // Empty
        $invalidAccountNumber2 = "11111111111111111"; // 17 digits

        $this->assertTrue(Validation::isValidAccountNumber($validAccountNumber), "An account number between 1 and 9 digits should pass");
        $this->assertFalse(Validation::isValidAccountNumber($invalidAccountNumber1), "An account number which is not between 1 and 9 digits should fail");
        $this->assertFalse(Validation::isValidAccountNumber($invalidAccountNumber2), "An account number which is not between 1 and 9 digits should fail");
    }

    public function testIsValidIPAddress() {
        $validIpAddress = "144.138.111.246";
        $invalidIpAddress1 = "localhost"; // localhost
        $invalidIpAddress2 = "Hello"; // completely wrong
        $invalidIpAddress3 = "2001:0db8:85a3:08d3:1319:8a2e:0370:7334"; // ipv6
        $invalidIpAddress4 = "192.168.1.1"; // local ip
        $invalidIpAddress5 = "10.0.0.1"; // reserved ip

        $this->assertTrue(Validation::isValidIPAddress($validIpAddress), "A valid public IPv4 address should pass");
        $this->assertFalse(Validation::isValidIPAddress($invalidIpAddress1), "An IP address that is private, reserved or IPv6 should fail");
        $this->assertFalse(Validation::isValidIPAddress($invalidIpAddress2), "An IP address that is private, reserved or IPv6 should fail");
        $this->assertFalse(Validation::isValidIPAddress($invalidIpAddress3), "An IP address that is private, reserved or IPv6 should fail");
        $this->assertFalse(Validation::isValidIPAddress($invalidIpAddress4), "An IP address that is private, reserved or IPv6 should fail");
        $this->assertFalse(Validation::isValidIPAddress($invalidIpAddress5), "An IP address that is private, reserved or IPv6 should fail");
    }

    public function testIsValidZipCode() {
        $validZipCode = "3000";
        $longZipCode = "";
        for($i = 0; $i < 31; $i++) {
            $longZipCode .= ".";
        }

        $this->assertTrue(Validation::isValidZipCode($validZipCode), "A zip code of less than 30 characters should pass");
        $this->assertFalse(Validation::isValidZipCode($longZipCode), "A zip code of more than 30 characters should fail");
    }

    public function testIsValidTown() {
        $validTown = "Melbourne";
        $longTown = "";
        for($i = 0; $i < 61; $i++) {
            $longTown .= ".";
        }

        $this->assertTrue(Validation::isValidTown($validTown), "A town with less than or equal to 60 characters should pass");
        $this->assertFalse(Validation::isValidTown($longTown), "A town with more than 60 characters should fail");
    }

    public function testIsValidBillingCountry() {
        $validCountry1 = "AU";
        $validCountry2 = "AUS";
        $invalidCountry = "AUSTRALIA";

        $this->assertTrue(Validation::isValidBillingCountry($validCountry1), "Billing country with 2 characters should pass");
        $this->assertTrue(Validation::isValidBillingCountry($validCountry2), "Billing country with 3 characters should pass");
        $this->assertFalse(Validation::isValidBillingCountry($invalidCountry), "Billing country with more than 3 characters should fail");
    }

    public function testIsValidDeliveryCountry() {
        $validCountry1 = "AU";
        $validCountry2 = "AUS";
        $invalidCountry = "AUSTRALIA";

        $this->assertTrue(Validation::isValidDeliveryCountry($validCountry1), "Delivery country with 2 characters should pass");
        $this->assertTrue(Validation::isValidDeliveryCountry($validCountry2), "Delivery country with 3 characters should pass");
        $this->assertFalse(Validation::isValidDeliveryCountry($invalidCountry), "Delivery country with more than 3 characters should fail");
    }

    public function testIsValidEmailAddress() {
        $validEmailAddress = "testemail@test.com";
        $invalidEmailAddress1 = "invalid.com.au";
        $invalidEmailAddress2 = "invalidemail@";

        $this->assertTrue(Validation::isValidEmailAddress($validEmailAddress), "Valid email address should pass");
        $this->assertFalse(Validation::isValidEmailAddress($invalidEmailAddress1,"An invalid email address should fail"));
        $this->assertFalse(Validation::isValidEmailAddress($invalidEmailAddress2,"An invalid email address should fail"));
    }

    public function testIsSupportedCard() {
        $supportedCard = "4444333322221111"; // visa
        $unsupportedCard = "6212341111111111111"; // union pay

        $this->assertTrue(Validation::isSupportedCard($supportedCard), "Supported card should pass");
        $this->assertFalse(Validation::isSupportedCard($unsupportedCard), "Unsupported card should fail");
    }

    public function testIsSupportedCurency() {
        $supportedCurrency = "USD";
        $unsupportedCurrency = "CHD";

        $this->assertTrue(Validation::isSupportedCurency($supportedCurrency), "Supported currency should pass");
        $this->assertFalse(Validation::isSupportedCurency($unsupportedCurrency), "Unsupported currency should fail");
    }

    public function testGetProperExiryMonth() {
        $expiryMonth1 = 2;
        $expiryMonth2 = "2";
        $expiryMonth3 = "02";
        $expiryMonth4 = "0002";

        $expected = "02";

        $this->assertSame($expected, Validation::getProperExiryMonth($expiryMonth1), "Expiry month should return " . $expected);
        $this->assertSame($expected, Validation::getProperExiryMonth($expiryMonth2), "Expiry month should return " . $expected);
        $this->assertSame($expected, Validation::getProperExiryMonth($expiryMonth3), "Expiry month should return " . $expected);
        $this->assertSame($expected, Validation::getProperExiryMonth($expiryMonth4), "Expiry month should return " . $expected);
    }

    public function testGetProperExpiryYear() {
        $pass1 = ["test" => "2057",
            "expected" => "2057"];
        $pass2 = ["test" => date("Y"),
            "expected" => "2016"];
        $pass3 = ["test" => "18",
            "expected" => "2018"];

        $this->assertSame($pass1["expected"], Validation::getProperExpiryYear($pass1["test"]), "Expiry year " . $pass1["test"] . " should return " . $pass1["expected"]);
        $this->assertSame($pass2["expected"], Validation::getProperExpiryYear($pass2["test"]), "Expiry year " . $pass2["test"] . " should return " . $pass2["expected"]);
        $this->assertSame($pass3["expected"], Validation::getProperExpiryYear($pass3["test"]), "Expiry year " . $pass3["test"] . " should return " . $pass3["expected"]);
    }

    public function testIsDateInFuture() {
        $now = new DateTime();
        $tomorrow = new DateTime("tomorrow");
        $yesterday = new DateTime("yesterday");

        $this->assertTrue(Validation::isDateInFuture($tomorrow), "A date in the future should pass");
        $this->assertFalse(Validation::isDateInFuture($now), "A date that is not in the future should fail");
        $this->assertFalse(Validation::isDateInFuture($yesterday), "A date that is not in the future should fail");
    }

    public function testIsValidClientId() {
        $validClientId = "testClientId";
        $invalidClientId1 = "test client id";
        $invalidClientId2 = "";
        $longClientId = "";
        for($i = 0; $i < 22; $i++) {
            $longClientId .= ".";
        }

        $this->assertTrue(Validation::isValidClientId($validClientId), "A client Id with no spaces and is between 1 - 20 characters should pass");
        $this->assertFalse(Validation::isValidClientId($invalidClientId1), "A client Id with spaces should fail");
        $this->assertFalse(Validation::isValidClientId($invalidClientId2), "An empty client Id should fail");
        $this->assertFalse(Validation::isValidClientId($longClientId), "A client Id which exceeds 20 characters should fail.");
    }
}