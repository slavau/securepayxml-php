<?php

namespace SecurePay\XMLAPI\Requests\Periodic;

use Sabre\Xml\Writer;
use SecurePay\XMLAPI\Exceptions\FeatureUnsupportedException;
use SecurePay\XMLAPI\Exceptions\IncompleteMessageException;
use SecurePay\XMLAPI\Exceptions\TxnTypeNotSupported;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\AddFuturePaymentPeriodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\AddOngoingPaymentPeriodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\AddPayorPeriodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\DeletePeriodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\EditPeriodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\Periodic;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\PeriodicRequestTypes;
use SecurePay\XMLAPI\Requests\Periodic\PeriodicItem\TriggerPeriodic;
use SecurePay\XMLAPI\Requests\Request;
use SecurePay\XMLAPI\Utils\ApiUtils;
use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Utils;

/**
 * Represents a periodic request to SecurePay
 *
 * Class PeriodicRequest
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic
 */
class PeriodicRequest extends Request
{
    /**
     * @var array Should contain the periodic list of the request
     */
    private $periodicList;

    /**
     * PaymentRequest constructor.
     * @param string $merchantId
     * @param string $txnPassword
     * @param bool $testMode
     */
    public function __construct($merchantId, $txnPassword, $testMode)
    {
        parent::__construct($merchantId, $txnPassword, $testMode);
        $this->periodicList = [];
    }

    public function getFullApiUrl() {
        if (sizeOf($this->periodicList) < 1) {
            throw new IncompleteMessageException("No periodic items have been added");
        }
        return ($this->testMode ? Configurations::getConfig("base_test_url") : Configurations::getConfig("base_live_url")) . "/xmlapi/periodic";
    }

    public function getRequestType() {
        return "Periodic";
    }

    public function generateRequestMessage() {
        if (sizeOf($this->periodicList) == 0) {
            throw new IncompleteMessageException("No periodic items have been added");
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
        $writer->writeElement("apiVersion", "spxml-3.0"); // Different value for payment..
        $writer->endElement(); // Close MessageInfo
        $writer->startElement("MerchantInfo");
        $writer->writeElement("merchantID", $this->merchantId);
        $writer->writeElement("password", $this->txnPassword);
        $writer->endElement(); // Close MerchantInfo
        $writer->writeElement("RequestType", $this->getRequestType());
        $writer->startElement($this->getRequestType());
        $writer->startElement("PeriodicList");
        $writer->writeAttribute("count", sizeOf($this->periodicList));
        for ($i = 0; $i < sizeof($this->periodicList); $i++) {
            $writer->startElement("PeriodicItem");
            $writer->writeAttribute("ID", $i + 1);
            $writer->write($this->periodicList[$i]->generateRequestObject());
            $writer->endElement(); // Close PeriodicItem
        }
        $writer->endElement(); // Close PeriodicList
        $writer->endElement(); // Close Periodic
        $writer->endElement(); // Close securepayMessage
        return $writer->outputMemory();
    }

    public function isReadyToGenerate() {
        if (sizeOf($this->periodicList) == 0) {
            return false;
        }
        for ($i = 0; $i < sizeof($this->periodicList); $i++) {
            if (!$this->periodicList[$i]->isAllRequiredValuesSet()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds a new periodic item to the periodic list
     *
     * @param Periodic $periodicItem A PeriodicItem/Periodic sub-class
     * @return AddFuturePaymentPeriodic|AddOngoingPaymentPeriodic|AddPayorPeriodic|DeletePeriodic|EditPeriodic|TriggerPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewPeriodicItem(Periodic $periodicItem) {
        if (sizeOf($this->periodicList) >= 1) { // Potentially a feature provided by SecurePay in the future.
            throw new FeatureUnsupportedException("Securepay restriction - 1 periodic item per request.");
        }
        if (!($periodicItem instanceof Periodic)) {
            throw new TxnTypeNotSupported("Periodic item type is not supported.");
        }
        $this->periodicList[] = $periodicItem;
        return $periodicItem;
    }

    /**
     * Creates a new Add Payor request and adds it to the periodic action list
     *
     * @return AddPayorPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewAddPayorRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::ADD_PAYOR));
    }

    /**
     * Creates a new Add future payment request and adds it to the periodic action list
     *
     * @return AddFuturePaymentPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewAddFuturePaymentRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::ADD_FUTURE_PAYMENT));
    }

    /**
     * Creates a new Add Ongoing Payment request and adds it to the periodic action list
     *
     * @return AddOngoingPaymentPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewAddOngoingPaymentRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::ADD_ONGOING_PAYMENT));
    }

    /**
     * Creates a new Delete periodic request and adds it to the periodic action list
     *
     * @return DeletePeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewDeleteRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::DELETE));
    }

    /**
     * Creates a new triggered periodic request and adds it to the periodic action list
     *
     * @return TriggerPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewTriggerRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::TRIGGER));
    }

    /**
     * Creates a new Edit periodic request and adds it to the periodic action list
     *
     * @return EditPeriodic
     * @throws FeatureUnsupportedException When attempting to add more than 1 periodic items to the list.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     */
    public function addNewEditRequest() {
        return $this->addNewPeriodicItem(self::getNewPeriodicByType(self::EDIT));
    }

    const ADD_PAYOR = "addPayor";
    const ADD_FUTURE_PAYMENT = "addFuturePayment";
    const ADD_ONGOING_PAYMENT = "addOngoingPayment";
    const DELETE = "delete";
    const TRIGGER = "trigger";
    const EDIT = "edit";

    /**
     * Returns a sub-class of the Periodic object which matches with the periodic type
     *
     * @param string $periodicType The periodic type of the transaction.
     * @throws TxnTypeNotSupported When the periodic type cannot be mapped to a PeriodicItem/Periodic sub-class
     * @return AddFuturePaymentPeriodic|AddOngoingPaymentPeriodic|AddPayorPeriodic|DeletePeriodic|EditPeriodic|TriggerPeriodic
     */
    public static function getNewPeriodicByType($periodicType) {
        switch(strtolower($periodicType)) {
            case strtolower(self::ADD_PAYOR): return new AddPayorPeriodic();
            case strtolower(self::ADD_FUTURE_PAYMENT): return new AddFuturePaymentPeriodic();
            case strtolower(self::ADD_ONGOING_PAYMENT): return new AddOngoingPaymentPeriodic();
            case strtolower(self::DELETE): return new DeletePeriodic();
            case strtolower(self::TRIGGER): return new TriggerPeriodic();
            case strtolower(self::EDIT): return new EditPeriodic();
            default: throw new TxnTypeNotSupported("Periodic type [".$periodicType."] is not supported. Refer to PeriodicRequestTypes.");
        }
    }
}