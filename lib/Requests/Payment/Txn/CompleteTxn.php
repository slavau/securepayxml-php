<?php

namespace SecurePay\XMLAPI\Requests\Payment\Txn;

use SecurePay\XMLAPI\Requests\Payment\PaymentRequest;
use SecurePay\XMLAPI\Utils\Configurations;
use SecurePay\XMLAPI\Utils\Validation;

/**
 * Processes a complete against a previous preauthorisation.
 *
 * Processes a complete against a previous preauthorisation. Only one complete may be processed against each preauthorisation.
 *
 * Class CompleteTxn
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Payment\Txn
 */
class CompleteTxn extends Txn
{
    /**
     * @var string Should contain the original preauthorisation Id for completion.
     */
    private $preauthId;

    public function __construct($preauthId = null)
    {
        parent::__construct();
        $this->preauthId = $preauthId;
    }

    protected function getTxnType()
    {
        return PaymentRequest::COMPLETE;
    }

    public function generateRequestObject()
    {
        $txnObj = ["txnType" => $this->getTxnType(),
            "txnSource" => Configurations::getConfig("default_txn_source"),
            "amount" => $this->getAmount(),
            "currency" => $this->getCurrency(),
            "purchaseOrderNo" => $this->getPurchaseOrderNo(),
            "preauthID" => $this->getPreauthId()
        ];

        return $txnObj;
    }

    protected function txnReady()
    {
        if (!isset($this->preauthId)) {
            return false;
        }
        return true;
    }

    /**
     * Returns the preauth Id which should match with the preauth Id of the original transaction to complete.
     *
     * @return string Preauthorisation Id
     */
    public function getPreauthId()
    {
        return $this->preauthId;
    }

    /**
     * Sets the preauthorisation Id
     *
     * @throws \InvalidArgumentException When the preauthorisation ID is not 6 characters
     * @param string $preauthId The Id of the original preauthorised transaction
     * @return $this
     */
    public function setPreauthId($preauthId)
    {
        if (!Validation::isValidPreauthId($preauthId)) {
            throw new \InvalidArgumentException("Invalid preauth ID");
        }
        $this->preauthId = $preauthId;
        return $this;
    }

    public function getApiUrl()
    {
        return "/xmlapi/payment";
    }
}