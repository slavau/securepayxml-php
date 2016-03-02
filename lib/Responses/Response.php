<?php

namespace SecurePay\XMLAPI\Responses;
use SecurePay\XMLAPI\Utils\Configurations;

/**
 * Reads the XML response from the server.
 *
 * Class Response
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Responses
 */
class Response
{
    /**
     * @var string The status code
     */
    private $statusCode;

    /**
     * @var string The description for the status code
     */
    private $statusDesc;

    /**
     * @var array The actions in the XML API response
     */
    private $actionList;

    /**
     * @var \SimpleXMLElement The DOM for the XML API response.
     */
    private $responseArray;

    /**
     * @var bool Identifies whether our request list was processed.
     */
    private $requestProcessed;

    /**
     * Response constructor.
     * @param string $xmlResponse The XML API response from SecurePay
     */
    public function __construct($xmlResponse)
    {
        $this->requestProcessed = false;
        $this->responseArray = simplexml_load_string($xmlResponse);
        $this->statusCode = strval($this->responseArray->Status->statusCode);
        $this->statusDesc = strval($this->responseArray->Status->statusDescription);
        $this->actionList = [];
        // Checking 0, 00 and 000 as SecurePay can return any of the three variants...
        if ($this->statusCode == "0" ||
            $this->statusCode == "00" ||
            $this->statusCode == "000") {
            $this->requestProcessed = true;
            $path = null;
             if ($this->responseArray->RequestType == "Periodic") {
                $path = $this->responseArray->Periodic->PeriodicList->PeriodicItem;
             } else if ($this->responseArray->RequestType == "Payment") {
                 $path = $this->responseArray->Payment->TxnList->Txn;
             } else if ($this->responseArray->RequestType == "addToken" || $this->responseArray->RequestType == "lookupToken") {
                 $path = $this->responseArray->Token->TokenList->TokenItem;
             }
            foreach ($path as $actionItem) {
                $this->actionList[] = $actionItem;
            }
        }
    }

    /**
     * Returns true if all actions on the list are approved.
     *
     * @return bool If all actions are approved
     */
    public function isAllApproved() {
        if (sizeOf($this->actionList) == 0) {
            return false;
        }
        foreach($this->actionList as $action) {
            $responseCode = strval($action->responseCode);
            if (!in_array($responseCode, Configurations::getConfig("approved_codes"))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convenience function for checking to see if first action in the request has been approved.
     *
     * @return bool If first request has been approved.
     */
    public function isFirstItemApproved() {
        if (sizeOf($this->actionList) == 0) {
            return false;
        }
        if (isset($this->actionList[0]->responseCode)) {
            $responseCode = strval($this->actionList[0]->responseCode);
            if (in_array($responseCode, Configurations::getConfig("approved_codes"))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the transaction's response code.
     *
     * @return null|string The response code
     */
    public function getFirstItemResponseCode() {
        if (!isset($this->actionList[0]->responseCode)) {
            return null;
        }
        return strval($this->actionList[0]->responseCode);
    }

    /**
     * Returns the transaction's response description
     *
     * @return null|string The response description
     */
    public function getFirstItemResponseText() {
        if (!isset($this->actionList[0]->responseText)) {
            return null;
        }
        return strval($this->actionList[0]->responseText);
    }

    /**
     * Returns the transaction's txnID which can be used for refunds subsequently.
     *
     * @return null|string The transaction Id
     */
    public function getFirstItemTxnId() {
        if (!isset($this->actionList[0]->txnID)) {
            return null;
        }
        return strval($this->actionList[0]->txnID);
    }

    /**
     * Returns the preauthID which can be used for complete requests subsequently.
     *
     * @return null|string The preauthorisation Id
     */
    public function getFirstItemPreauthId() {
        if (!isset($this->actionList[0]->preauthID)) {
            return null;
        }
        return strval($this->actionList[0]->preauthID);
    }

    /**
     * Returns the customer reference number of the transaction which can be used to trigger payments at a later date.
     *
     * @return null|string The client Id or customer reference number
     */
    public function getFirstItemClientId() {
        if (!isset($this->actionList[0]->clientID)) {
            return null;
        }
        return strval($this->actionList[0]->clientID);
    }

    /**
     * Returns the token value of a customer's card which can be used to trigger payments at a later date.
     *
     * @return null|string The token value
     */
    public function getFirstItemTokenValue() {
        if (!isset($this->actionList[0]->tokenValue)) {
            return null;
        }
        return strval($this->actionList[0]->tokenValue);
    }

    /**
     * Returns the status code
     *
     * @return string The status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returrns The status description
     *
     * @return string The status description
     */
    public function getStatusDesc()
    {
        return $this->statusDesc;
    }

    /**
     * Returns the array of actions in the list. (Usually will only return 1 due to SecurePay's restriction).
     *
     * @return array The action list
     */
    public function getActionList()
    {
        return $this->actionList;
    }

    /**
     * Returns the full XML DOM object from SecurePay's XML API.
     *
     * @return \SimpleXMLElement The XML array object.
     */
    public function getResponseArray()
    {
        return $this->responseArray;
    }

    /**
     * Returns true if the status description was 0 (Normal).
     *
     * @return boolean
     */
    public function isRequestProcessed()
    {
        return $this->requestProcessed;
    }
}