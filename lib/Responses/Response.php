<?php

namespace SecurePay\XMLAPI\Responses;

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
     * Response constructor.
     * @param string $xmlResponse The XML API response from SecurePay
     */
    public function __construct($xmlResponse)
    {
        $this->responseArray = simplexml_load_string($xmlResponse);
        $this->statusCode = $this->responseArray->Status->statusCode;
        $this->statusDesc = $this->responseArray->Status->statusDescription;
        $this->actionList = [];
        if ($this->statusCode == "0" ||
            $this->statusCode == "00" ||
            $this->statusCode == "000") {
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
            if ($action->responseCode != "0" &&
                $action->responseCode != "00" &&
                $action->responseCode != "000") {
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
    public function isFirstRequestApproved() {
        if (sizeOf($this->actionList) == 0) {
            return false;
        }
        if ($this->actionList[0]->responseCode != "0" &&
            $this->actionList[0]->responseCode != "00" &&
            $this->actionList[0]->responseCode != "000") {
            return false;
        }
        return true;
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


}