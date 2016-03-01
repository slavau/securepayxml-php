<?php

namespace SecurePay\XMLAPI\Requests\Payment;

use Sabre\Xml\Writer;
use SecurePay\XMLAPI\Exceptions\FeatureUnsupportedException;
use SecurePay\XMLAPI\Exceptions\IncompleteMessageException;
use SecurePay\XMLAPI\Exceptions\TxnTypeNotSupported;
use SecurePay\XMLAPI\Requests\Payment\Txn\CompleteTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\DEDirectCreditTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\DEDirectDebitTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\FraudguardOnlyTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\PreauthoriseTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\RefundTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\StandardPaymentTxn;
use SecurePay\XMLAPI\Requests\Payment\Txn\Txn;
use SecurePay\XMLAPI\Requests\Payment\Txn\TxnRequestTypes;
use SecurePay\XMLAPI\Requests\Request;
use SecurePay\XMLAPI\Utils\ApiUtils;
use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Utils;

/**
 * Represents a transaction request to SecurePay
 *
 * Class PaymentRequest
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment
 */
class PaymentRequest extends Request
{
    /**
     * @var array Should contain the transaction list of the request
     */
    private $txnList;

    /**
     * PaymentRequest constructor.
     * @param string $merchantId
     * @param string $txnPassword
     * @param bool $testMode
     */
    public function __construct($merchantId, $txnPassword, $testMode)
    {
        parent::__construct($merchantId, $txnPassword, $testMode);
        $this->txnList = [];
    }

    public function getFullApiUrl() {
        if (sizeOf($this->txnList) < 1) {
            throw new IncompleteMessageException("No transactions have been added");
        }
        return ($this->testMode ? Configurations::getConfig("base_test_url") : Configurations::getConfig("base_live_url")) . $this->txnList[0]->getApiUrl(); // return the URL of the first transaction request
    }

    public function getRequestType() {
        return "Payment";
    }

    public function generateRequestMessage() {
        if (sizeOf($this->txnList) == 0) {
            throw new IncompleteMessageException("No transactions have been added");
        }
        $writer = new Writer();
        $writer->openMemory();
        $writer->setIndent(Configurations::getConfig("use_indentation"));
        $writer->setIndentString(Configurations::getConfig("indentation_string"));
        $writer->startDocument(Configurations::getConfig("xml_version"), Configurations::getConfig("charset_encoding"));
        $writer->startElement("SecurePayMessage");
        $writer->startElement("MessageInfo");
        $writer->writeElement("messageID", ApiUtils::getRandomMessageId(30, 30));
        $writer->writeElement("messageTimestamp", $this->timestamp);
        $writer->writeElement("timeoutValue", Configurations::getConfig("timeout_value"));
        $writer->writeElement("apiVersion", "xml-4.2"); // Different value for periodic..
        $writer->endElement(); // Close MessageInfo
        $writer->startElement("MerchantInfo");
        $writer->writeElement("merchantID", $this->merchantId);
        $writer->writeElement("password", $this->txnPassword);
        $writer->endElement(); // Close MerchantInfo
        $writer->writeElement("RequestType", $this->getRequestType());
        $writer->startElement($this->getRequestType());
        $writer->startElement("TxnList");
        $writer->writeAttribute("count", sizeOf($this->txnList));
        for ($i = 0; $i < sizeof($this->txnList); $i++) {
            $writer->startElement("Txn");
            $writer->writeAttribute("ID", $i + 1);
            $writer->write($this->txnList[$i]->generateRequestObject());
            $writer->endElement(); // Close Txn
        }
        $writer->endElement(); // Close TxnList
        $writer->endElement(); // Close Payment
        $writer->endElement(); // Close securepayMessage
        return $writer->outputMemory();
    }

    public function isReadyToGenerate() {
        if (sizeOf($this->txnList) == 0) {
            return false;
        }
        for ($i = 0; $i < sizeof($this->txnList); $i++) {
            if (!$this->txnList[$i]->isAllRequiredValuesSet()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds a new transaction to the transaction list
     *
     * @param Txn $txn A Txn sub-class
     * @return CompleteTxn|DEDirectCreditTxn|DEDirectDebitTxn|FraudguardOnlyTxn|PreauthoriseTxn|RefundTxn|StandardPaymentTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewTxn(Txn $txn) {
        if (sizeOf($this->txnList) >= 1) { // Potentially a feature provided by SecurePay in the future.
            throw new FeatureUnsupportedException("Securepay restriction - 1 transaction per request.");
        }
        if (!($txn instanceof Txn)) {
            throw new TxnTypeNotSupported("Transaction type is not supported.");
        }
        $this->txnList[] = $txn;
        return $txn;
    }

    /**
     * Creates a new Standard payment request
     *
     * @return StandardPaymentTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewStandardPaymentRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::STANDARD_PAYMENT));
    }

    /**
     * Creates a new Refund request and adds it to the transaction list
     *
     * @return RefundTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewRefundRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::REFUND));
    }

    /**
     * Creates a new preauthorisation request and adds it to the transaction list
     *
     * @return PreauthoriseTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewPreauthorisationRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::PREAUTHORISE));
    }

    /**
     * Creates a new complete request and adds it to the transaction list
     *
     * @return CompleteTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewCompleteRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::COMPLETE));
    }

    /**
     * Creates a new Fraudguard only request and adds it to the transaction list
     *
     * @return FraudguardOnlyTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewFraudguardOnlyRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::FRAUDGUARD_ONLY));
    }

    /**
     * Creates a new Direct Debit request and adds it to the transaction list
     *
     * @return DEDirectDebitTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewDirectDebitRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::DE_DIRECT_DEBIT));
    }

    /**
     * Creates a new Direct Credit request and adds it to the transaction list
     *
     * @return DEDirectCreditTxn
     * @throws FeatureUnsupportedException When attempting to add more than 1 transaction to the list.
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     */
    public function addNewDirectCreditRequest() {
        return $this->addNewTxn(self::getNewTxnByType(self::DE_DIRECT_CREDIT));
    }

    const STANDARD_PAYMENT = "0";
    const REFUND = "4";
    const PREAUTHORISE = "10";
    const COMPLETE = "11";
    const FRAUDGUARD_ONLY = "12";
    const DE_DIRECT_DEBIT = "15";
    const DE_DIRECT_CREDIT = "17";

    /**
     * Returns a sub-class of the Txn object which matches with the txnType
     *
     * @param int $txnVal The txnType
     * @throws TxnTypeNotSupported When the txnType cannot be mapped to a Txn\Txn sub-class
     * @return CompleteTxn|DEDirectCreditTxn|DEDirectDebitTxn|FraudguardOnlyTxn|PreauthoriseTxn|RefundTxn|StandardPaymentTxn A sub-class of the Txn object
     */
    public static function getNewTxnByType($txnVal) {
        switch($txnVal) {
            case self::STANDARD_PAYMENT: return new StandardPaymentTxn();
            case self::REFUND: return new RefundTxn();
            case self::PREAUTHORISE: return new PreauthoriseTxn();
            case self::COMPLETE: return new CompleteTxn();
            case self::FRAUDGUARD_ONLY: return new FraudguardOnlyTxn();
            case self::DE_DIRECT_DEBIT: return new DEDirectDebitTxn();
            case self::DE_DIRECT_CREDIT: return new DEDirectCreditTxn();
            default: throw new TxnTypeNotSupported("Transaction type [".$txnVal."] is not supported. Refer to TxnRequestTypes.");
        }
    }
}