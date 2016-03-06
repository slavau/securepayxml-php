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
      - [**Once off payments**](#once-off-payments)
      - [**Refunding payments**](#refunding-payments)
      - [**Preauthorising cards**](#preauthorising-cards)
      - [**Completing/capturing preauthorisations**](#completingcapturing-preauthorisations)
      - [**Direct debit**](#direct-debit)
      - [**Direct credit**](#direct-credit)
      - [**Fraudguard only**](#fraudguard-only)
    - [**Periodic requests**](#periodic-requests)
      - [**Adding a payor**](#adding-a-payor)
      - [**Deleting a payor / schedule**](#deleting-a-payor--schedule)
      - [**Editing a payor / schedule**](#editing-a-payor--schedule)
      - [**Triggering a payment against a payor Id**](#triggering-a-payment-against-a-payor-id)
      - [**Adding a once off future payment**](#adding-a-once-off-future-payment)
      - [**Adding an ongoing payment**](#adding-an-ongoing-payment)
        - [Schedule by X amount of days](#schedule-by-x-amount-of-days)
        - [Schedule by calendar interval](#schedule-by-calendar-interval)
  - [Minimum message requirements](#minimum-message-requirements)
    - [**Payment requests**](#payment-requests-1)
      - [Once off payments - StandardPaymentTxn()](#once-off-payments---standardpaymenttxn)
      - [Refunds - RefundTxn()](#refunds---refundtxn)
      - [Preauthorisations - PreauthoriseTxn()](#preauthorisations---preauthorisetxn)
      - [Completes / Captures - CompleteTxn()](#completes--captures---completetxn)
      - [Direct Debits - DEDirectDebitTxn()](#direct-debits---dedirectdebittxn)
      - [Direct Credits - DEDirectCreditTxn()](#direct-credits---dedirectcredittxn)
      - [Fraudguard only - FraudguardOnlyTxn()](#fraudguard-only---fraudguardonlytxn)
    - [**Periodic requests**](#periodic-requests-1)
      - [Adding a payor - AddPayorPeriodic()](#adding-a-payor---addpayorperiodic)
      - [Deleting a payor - DeletePeriodic()](#deleting-a-payor---deleteperiodic)
      - [Editing a payor - EditPeriodic()](#editing-a-payor---editperiodic)
      - [Triggering a payment against a payor - TriggerPeriodic()](#triggering-a-payment-against-a-payor---triggerperiodic)
      - [Scheduling a once off future payment - AddFuturePaymentPeriodic()](#scheduling-a-once-off-future-payment---addfuturepaymentperiodic)
      - [Scheduling an ongoing payment - AddOngoingPaymentPeriodic()](#scheduling-an-ongoing-payment---addongoingpaymentperiodic)


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

The payment request takes in 3 parameters -
`new PaymentRequest(merchantId: string, txnPassword : string, testMode : bool);`

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

### **Periodic requests**

SecurePay's periodic server can handle the following requests:

 - Adding a payor
 - Deleting a payor
 - Editing a payor
 - Triggering a payment against a payor
 - Scheduling a once off future payment
 - Scheduling an ongoing payment

SecurePay has many different terminologies for the '*payor*'. A payor is a reference which maps back to a Credit Card number which was previously added. This can either be called the *client Id, token, customer reference number (crn) or payor*. Generally you will be calling setClientId(string: payor) to set the payor Id.

To begin creating a request to the Periodic server, you will need to create a PeriodicRequest object by declaring the following namespace to your file

    use SecurePay\XMLAPI\Requests\Periodic\PeriodicRequest;

The periodic request takes in 3 parameters -
`new PeriodicRequest(merchantId: string, txnPassword : string, testMode : bool);`

#### **Adding a payor**

The below request will add a new payor token with the payor Id as **test payor 123** to SecurePay's system. This token or reference can then be used at a later time to trigger a payment against without providing the Credit Card number again.

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
	$addPayorRequest = $periodicRequest->addNewAddPayorRequest();
	$addPayorRequest->setClientId("test-payor-123")
					->setCreditCardNo("4444333322221111")
					->setExpiryMonth("06")
					->setExpiryYear("2017")
					->setAmount("100");
	$response = $periodicRequest->sendXMLRequest();
	if ($response->isRequestProcessed()) {
		if ($response->isFirstItemApproved()) {
			echo "Add payor request has been accepted";
		} else {
			echo "Add payor request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
		}
	} else {
		echo "Add payor request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
	}


#### **Deleting a payor / schedule**

The request message below will delete a payor **test payor 123** which was previously stored against the merchant Id. You can also delete schedules based on the payor Id with this message.

    $periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
	$deletePayorRequest = $periodicRequest->addNewDeleteRequest();
	$deletePayorRequest->setClientId("test-payor-123");
	$response = $periodicRequest->sendXMLRequest();
	if ($response->isRequestProcessed()) {
		if ($response->isFirstItemApproved()) {
			echo "Delete request has been accepted";
		} else {
			echo "Delete request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
		}
	} else {
		echo "Delete request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
	}

#### **Editing a payor / schedule**

The edit request message will allow you to edit a payor's Credit Card or Direct Entry details. The below message edits the existing payor's details to:

Credit card number: 4242424242424242
Expiry month: 05
Expiry year: 2018

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
    $editRequest = $periodicRequest->addNewEditRequest();
    $editRequest->setClientId("test-payor-123")
                ->setCreditCardNo("4242424242424242")
                ->setExpiryMonth("05")
                ->setExpiryYear("2018");
    $response = $periodicRequest->sendXMLRequest();
    if ($response->isRequestProcessed()) {
        if ($response->isFirstItemApproved()) {
            echo "Edit payor request has been accepted";
        } else {
            echo "Edit payor request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
        }
    } else {
        echo "Edit payor request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
    }

#### **Triggering a payment against a payor Id**

The below request message will trigger a payment against the payor **test payor 123** for the amount of $1.00.

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
    $triggerRequest = $periodicRequest->addNewTriggerRequest();
    $triggerRequest->setClientId("test-payor-123")
                    ->setAmount("100")
                    ->setPurchaseOrderNo("Test triggered payment");
    $response = $periodicRequest->sendXMLRequest();
    if ($response->isRequestProcessed()) {
        if ($response->isFirstItemApproved()) {
            echo "Triggered payment has been approved. Bank transaction Id is: " . $response->getFirstItemTxnId();
        } else {
            echo "Triggered payment request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
        }
    } else {
        echo "Triggered payment request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
    }

#### **Adding a once off future payment**

The below will add a new once off scheduled payment which will run on 01/04/2017 against the credit card number 4444-3333-2222-1111 for the amount of $1.00.

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
    $addFuturePaymentRequest = $periodicRequest->addNewAddFuturePaymentRequest();
    $addFuturePaymentRequest->setStartDate("01/04/2017")
					        ->setClientId("test-payor-1234")
					        ->setCreditCardNo("4444333322221111")
					        ->setExpiryMonth("06")
					        ->setExpiryYear("2017")
					        ->setAmount("100");
    $response = $periodicRequest->sendXMLRequest();
    if ($response->isRequestProcessed()) {
        if ($response->isFirstItemApproved()) {
            echo "Add future payment request has been accepted. The expected start date will be " . $response->getFirstItem()->startDate;
        } else {
            echo "Add future payment request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
        }
    } else {
        echo "Add future payment request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
    }
**NOTE: ** SecurePay does not allow you to reuse a payor reference as the client Id. You will need to provide a unique payor Id to create the schedule.

**NOTE: ** The setStartDate accepts either a DateTime object or a valid date string. Please visit http://php.net/manual/en/datetime.formats.date.php for all the valid date formats.

#### **Adding an ongoing payment**

This request allows you to schedule a payment to happen on an ongoing basis either based on the number of days elapsed or on a calender day.

##### Schedule by X amount of days

The following will charge the credit card number 4444333322221111 for the amount of $1.00 every 20 days from the date 01/04/2016.

You must call setDayIntervals(int) to set the number of days between a payment.

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
    $addPayorRequest = $periodicRequest->addNewAddOngoingPaymentRequest();
    $addPayorRequest->setStartDate(new DateTime("2016-04-01"))
		          ->setClientId("test-payor-1235")
		           ->setNumberOfPayments(5)
		           ->setCreditCardNo("4444333322221111")
		           ->setExpiryMonth("06")
		           ->setExpiryYear("2017")
		           ->setDayIntervals(20)
		           ->setAmount("100");
    $response = $periodicRequest->sendXMLRequest();
    if ($response->isRequestProcessed()) {
        if ($response->isFirstItemApproved()) {
            echo "Add ongoing payment request has been accepted. The expected start date will be " . $response->getFirstItem()->startDate;
        } else {
            echo "Add ongoing payment request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
        }
    } else {
        echo "Add ongoing request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
    }


##### Schedule by calendar interval

The following will charge the credit card number 4444333322221111 for the amount of $1.00 every 1 month.

You must call setScheduledInterval(string|int) to set the frequency of the payment.

All the intervals can be found in the AddOngoingPaymentPeriodic class as a const. Please find the valid intervals below:

    const CALENDAR_PAYMENT_INTERVAL_WEEKLY = "1";
    const CALENDAR_PAYMENT_INTERVAL_FORTNIGHTLY = "2";
    const CALENDAR_PAYMENT_INTERVAL_MONTHLY = "3";
    const CALENDAR_PAYMENT_INTERVAL_QUARTERLY = "4";
    const CALENDAR_PAYMENT_INTERVAL_HALFYEARLY = "5";
    const CALENDAR_PAYMENT_INTERVAL_ANUALLY = "6";


Here is the request message

	$periodicRequest = new PeriodicRequest("ABC0001", "abc123", true);
    $addOnGoingPaymentRequest = $periodicRequest->addNewAddOngoingPaymentRequest();
    $addOnGoingPaymentRequest->setStartDate(new DateTime("2016-04-01"))
					        ->setClientId("test-payor-1236")
					        ->setNumberOfPayments(5)
					        ->setCreditCardNo("4444333322221111")
					        ->setExpiryMonth("06")
					        ->setExpiryYear("2017")
					        ->setScheduledInterval(\SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\AddOngoingPaymentPeriodic::CALENDAR_PAYMENT_INTERVAL_MONTHLY)
					        ->setAmount("100");
    $response = $periodicRequest->sendXMLRequest();
    if ($response->isRequestProcessed()) {
        if ($response->isFirstItemApproved()) {
            echo "Add ongoing payment request has been accepted. The expected start date will be " . $response->getFirstItem()->startDate;
        } else {
            echo "Add ongoing payment request has been declined with following code: " . $response->getFirstItemResponseCode() . " - " . $response->getFirstItemResponseText();
        }
    } else {
        echo "Add ongoing request failed with following error: " . $response->getStatusCode() . " - ". $response->getStatusDesc();
    }

**NOTE: ** SecurePay does not allow you to reuse a payor reference as the client Id. You will need to provide a unique payor Id to create the schedule.

**NOTE: ** The setStartDate accepts either a DateTime object or a valid date string. Please visit http://php.net/manual/en/datetime.formats.date.php for all the valid date formats.

## Minimum message requirements

### **Payment requests**

#### Once off payments - StandardPaymentTxn()

<table>
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

<table>
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

<table>
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

<table>
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

<table>
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
        <td>BSB number</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number</td>
        <td>setAccountNumber(string)</td>
    </tr>
</table>

#### Direct Credits - DEDirectCreditTxn()

<table>
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

<table>
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

### **Periodic requests**

#### Adding a payor - AddPayorPeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
    <tr>
        <td>Amount to charge</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>Credit card number**</td>
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
        <td>BSB number**</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number**</td>
        <td>setAccountNumber(string)</td>
    </tr>
    <tr>
        <td>Account name**</td>
        <td>setAccountName(string)</td>
    </tr>
</table>

** Provide either complete credit card details or complete direct entry details. You do not have to provide complete information for both.

#### Deleting a payor - DeletePeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
</table>

#### Editing a payor - EditPeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
    <tr>
        <td>Credit card number**</td>
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
        <td>BSB number**</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number**</td>
        <td>setAccountNumber(string)</td>
    </tr>
    <tr>
        <td>Account name**</td>
        <td>setAccountName(string)</td>
    </tr>
</table>

** Provide either complete credit card details or complete direct entry details. You do not have to provide complete information for both.

#### Triggering a payment against a payor - TriggerPeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
    <tr>
        <td>Amount to charge</td>
        <td>setAmount(int|string)</td>
    </tr>
   <tr>
        <td>Purchase order number</td>
        <td>setPurchaseOrderNo(string)</td>
    </tr>
</table>

#### Scheduling a once off future payment - AddFuturePaymentPeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
    <tr>
        <td>Amount to charge</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>The starting date</td>
        <td>setStartDate(string|\DateTime)</td>
    </tr>
    <tr>
        <td>Credit card number**</td>
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
        <td>BSB number**</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number**</td>
        <td>setAccountNumber(string)</td>
    </tr>
    <tr>
        <td>Account name**</td>
        <td>setAccountName(string)</td>
    </tr>
</table>

** Provide either complete credit card details or complete direct entry details. You do not have to provide complete information for both.

#### Scheduling an ongoing payment - AddOngoingPaymentPeriodic()

<table>
	<tr>
		<th>Description</th>
		<th>Function name</th>
	</tr>
	 <tr>
        <td>The client Id</td>
        <td>setClientId(string)</td>
    </tr>
    <tr>
        <td>Amount to charge</td>
        <td>setAmount(int|string)</td>
    </tr>
    <tr>
        <td>The starting date</td>
        <td>setStartDate(string|\DateTime)</td>
    </tr>
    <tr>
        <td>The interval in days to take the payment***</td>
        <td>setDayIntervals(int)</td>
    </tr>
        <tr>
        <td>The interval based on calendar terms to take a payment ***</td>
        <td>setScheduledInterval(string|\DateTime)</td>
    </tr>
    <tr>
        <td>Credit card number**</td>
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
        <td>BSB number**</td>
        <td>setBsbNumber(string)</td>
    </tr>
    <tr>
        <td>Account number**</td>
        <td>setAccountNumber(string)</td>
    </tr>
    <tr>
        <td>Account name**</td>
        <td>setAccountName(string)</td>
    </tr>
</table>

** Provide either complete credit card details or complete direct entry details. You do not have to provide complete information for both.

\*\*\* Either provide the interval in days or provide the interval in calendar terms. If both are provided, the last set variable will be used.