<?php

namespace SecurePay\XMLAPI\Requests\Periodic\PeriodicItem;

/**
 * The delete function can be used to delete a future payment, payment schedule or mark a Payor as deleted.
 *
 * The delete function can be used to delete a future payment, payment schedule or mark a Payor as deleted.
 * When a Payor is marked as deleted you will still be able to see it through the merchant login,
 * however you will be able to re-use the Payor ID to store another set of customer details.
 *
 * Class DeletePeriodic
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests\Periodic\PeriodicItem
 */
class DeletePeriodic extends Periodic
{
    /**
     * @var string The payor's reference number.
     */
    private $clientId;

    public function __construct()
    {
        parent::__construct();
    }

    protected function periodicItemReady()
    {
        return true;
    }

    /**
     * Returns the payor's client Id which is used as a reference to trigger a payment
     *
     * @return string The payor's client Id
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets the payor's client Id
     *
     * @param $clientId The payor's client Id
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    protected function getPeriodicItemType()
    {
        return "delete";
    }

    public function generateRequestObject()
    {
        $txnObj = ["actionType" => $this->getPeriodicItemType(),
            "clientID" => $this->getClientId()];
        return $txnObj;
    }
}