# SecurePayXML-PHP
A simple and easy to use PHP wrapper for the SecurePay XML API.

Please visit https://www.securepay.com.au/ for further information regarding the SecurePay service or to sign up.

To check service availability, please see - http://securepay.statuspage.io/

Visit http://bengsspace.com/securepayxmlapi-php/docs/ for the API documentation.

## Table of contents

- [SecurePayXML-PHP](#securepayxml-php)
  - [Table of contents](#table-of-contents)
  - [Installation](#installation)
  - [Test details](#test-details)
  - [Examples](#examples)
    - [**Payment requests**](#payment-requests)
      - [Once off payments](#once-off-payments)
      - [Refunding payments](#refunding-payments)
      - [Preauthorising cards](#preauthorising-cards)
      - [Completing/capturing preauthorisations](#completingcapturing-preauthorisations)
      - [Direct debit](#direct-debit)
      - [Direct credit](#direct-credit)
      - [Fraudguard only](#fraudguard-only)
  - [Minimum message requirements](#minimum-message-requirements)
    - [**Payment requests**](#payment-requests-1)
      - [Once off payments - StandardPaymentTxn()](#once-off-payments---standardpaymenttxn)
      - [Refunds - RefundTxn()](#refunds---refundtxn)
      - [Preauthorisations - PreauthoriseTxn()](#preauthorisations---preauthorisetxn)
      - [Completes / Captures - CompleteTxn()](#completes--captures---completetxn)
      - [Direct Debits - DEDirectDebitTxn()](#direct-debits---dedirectdebittxn)
      - [Direct Credits - DEDirectCreditTxn()](#direct-credits---dedirectcredittxn)
      - [Fraudguard only - FraudguardOnlyTxn()](#fraudguard-only---fraudguardonlytxn)


## Installation

This wrapper is only compatible with the Composer package manager.

Run the following:

	composer require securepay/xmlapi-php
or add to your composer.json

    "require": {
         "securepay/xmlapi-php": "dev-master"
     }

## Test details

**API details**

    Merchant ID: ABC0001
    Test transaction password: abc123

**Test log in details**

    https://testlogin.securepay.com.au
    Merchant ID: ABC
    User name: test
    Password: abc1234!!

**Test credit card details**

    Credit card number: 4444333322221111
    Expiry date: 12/2018 (Anything in the future)
    CVV: 123 (Optional)

## Examples



### **Payment requests**

SecurePay's payment server can handle the following requests:

 - Once off payments
 - Refunds
 - Preauthorisations
 - Completes / Captures
 - Direct Debits
 - Direct Credits
 - Fraudguard only

To begin creating a request to the Payment server, you will need to create a PaymentRequest object by declaring the following namespace to your file

    use SecurePay\XMLAPI\Requests\Payment\PaymentRequest;

The payment request takes in 3 parameters - new PaymentRequest(merchantId: string, txnPassword : string, testMode : bool);

#### **Once off payments**

The below will process a transaction on the *test* environment with the test credit card details for the amount of $1.00 and will have "Test purchase order" as the reference.

	    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $standardPayment = $paymentRequest->addNewStandardPaymentRequest();
        $standardPayment->setPurchaseOrderNo("Test purchase order")
                        ->setAmount("100")
                        ->setCreditCardNo("4444333322221111")
                        ->setExpiryMonth("02")
                        ->setExpiryYear("2017");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Transaction was successful! Bank transaction ID is: " . $response->getFirstItemTxnId();
            } else {
                echo "Transaction declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Payment request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

**NOTE:** There will be a transaction ID that gets returned with the response. Please store that ID and the purchase order number in order to refund at a later time. In this particular instance, SecurePay has returned a bank transaction ID of **100651**. This ID is generally unique for each new transaction.

#### **Refunding payments**

The below is a refund request in the *test* environment which will reverse the above transaction

	    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $refundPayment = $paymentRequest->addNewRefundRequest();
        $refundPayment->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setTxnId("113933");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Refund was successful!";
            } else {
                echo "Refund declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Refund request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

#### **Preauthorising cards**

The below will process a preauthorisation on the *test* environment and reserve $1.00 on the the test credit card with the purchase order number of "Test purchase order".

	    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $preauth = $paymentRequest->addNewPreauthorisationRequest();
        $preauth->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setCreditCardNo("4444333322221111")
            ->setExpiryMonth("02")
            ->setExpiryYear("2017");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Preauthorisation was successful! Preauth code is: " . $response->getFirstItemPreauthId();
            } else {
                echo "Preauthorisation declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Preauthorisation request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

**NOTE:** There will be a preauth code that gets returned with the response. Please store that code and the purchase order number in order to complete the transaction at a later time. In this particular instance, SecurePay has returned a preauth code of **113925**. This ID is generally unique for each new preauthorisation.

#### **Completing/capturing preauthorisations**

The below is a complete request in the *test* environment which will capture the funds the above preauthorisation. As noted above for Once off payments, the complete request will also return a bank transaction Id which can be stored for refunding at a later time.

    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $completePreauth = $paymentRequest->addNewCompleteRequest();
        $completePreauth->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setPreauthId("113925");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Complete request was successful! Bank transaction ID is: " . $response->getFirstItemTxnId();
            } else {
                echo "Complete request declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Complete request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

#### **Direct debit**

The below is a request to charge a BSB number of 123456 and an account number of 111111 for the amount of $1.00.

    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $directDebit = $paymentRequest->addNewDirectDebitRequest();
        $directDebit->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setBsbNumber("123456")
            ->setAccountNumber("111111")
            ->setAccountName("Test person");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Direct Debit request was successful! Bank transaction ID is: " . $response->getFirstItemTxnId();
            } else {
                echo "Direct Debit request declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Direct Debit request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

#### **Direct credit**

The below is a request to credit a BSB number of 123456 and an account number of 111111 for the amount of $1.00.

    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $directCredit = $paymentRequest->addNewDirectCreditRequest();
        $directCredit->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setBsbNumber("123456")
            ->setAccountNumber("123456")
            ->setAccountName("Test person");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->isFirstItemApproved()) {
                echo "Direct Credit request was successful! Bank transaction ID is: " . $response->getFirstItemTxnId();
            } else {
                echo "Direct Credit request declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
            }
        } else {
            echo "Direct Credit request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

#### **Fraudguard only**

The below will process a transaction on the *test* environment with the test credit card details for the amount of $1.00 and will have "Test purchase order" as the reference. This request will not charge the customer and should only be used to validate the transaction's details against the Fraudguard system.

	    $paymentRequest = new PaymentRequest("ABC0001", "abc123", true);
        $standardPayment = $paymentRequest->addNewFraudguardOnlyRequest();
        $standardPayment->setPurchaseOrderNo("Test purchase order")
            ->setAmount("100")
            ->setCreditCardNo("4444333322221111")
            ->setExpiryMonth("02")
            ->setExpiryYear("2017")
            ->setIpAddress("143.234.211.122");
        $response = $paymentRequest->sendXMLRequest();
        if ($response->isRequestProcessed()) {
            if ($response->getFirstItem()->antiFraudResponseCode) {
                echo "Antifraud check was successful! Antifraud code is: " . $response->getFirstItem()->antiFraudResponseCode;
            } else {
                echo "Antifraud check declined with following code: " .  $response->getFirstItem()->antiFraudResponseCode . " - " . $response->getFirstItem()->antiFraudResponseText;
            }
        } else {
            echo "Antifraud check request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
        }

## Minimum message requirements


### **Payment requests**

#### Once off payments - StandardPaymentTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to charge</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>Credit card number</td>
        <td>setCreditCardNo(string)</td>
    </tr>
    <tr>
        <td>Expiry month**</td>
        <td>setExpiryMonth(int|string)</td>
    </tr>
    <tr>
        <td>Expiry year**</td>
        <td>setExpiryYear(int|string)</td>
    </tr>
</table>
** If the recurring flag is set to true, the expiry date becomes an optional field.

#### Refunds - RefundTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to refund</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Original purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>Original bank transaction Id</td>
        <td>setTxnId(string)</td>
    </tr>
</table>

#### Preauthorisations - PreauthoriseTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to preauthorise</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>Credit card number</td>
        <td>setCreditCardNo(string)</td>
    </tr>
    <tr>
        <td>Expiry month**</td>
        <td>setExpiryMonth(int|string)</td>
    </tr>
    <tr>
        <td>Expiry year**</td>
        <td>setExpiryYear(int|string)</td>
    </tr>
</table>
** If the recurring flag is set to true, the expiry date becomes an optional field.

#### Completes / Captures - CompleteTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to complete</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Original purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>Preauthorisation code</td>
        <td>setPreauthId(string)</td>
    </tr>
</table>

#### Direct Debits - DEDirectDebitTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to preauthorise</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>BSB number</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number</td>
        <td>setAccountNumber(string)</td>
    </tr>
</table>

#### Direct Credits - DEDirectCreditTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to preauthorise</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>BSB number</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number</td>
        <td>setAccountNumber(string)</td>
    </tr>
    <tr>
        <td>Account name</td>
        <td>setAccountName(string)</td>
    </tr>
</table>

#### Fraudguard only - FraudguardOnlyTxn()

> <table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
    <tr>
        <td>Amount to preauthorise</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
    <tr>
        <td>Credit card number</td>
        <td>setCreditCardNo(string)</td>
    </tr>
    <tr>
        <td>Expiry month**</td>
        <td>setExpiryMonth(int|string)</td>
    </tr>
    <tr>
        <td>Expiry year**</td>
        <td>setExpiryYear(int|string)</td>
    </tr>
    <tr>
        <td>IP address</td>
        <td>setIpAddress(string)</td>
    </tr>
</table>
** If the recurring flag is set to true, the expiry date becomes an optional field.