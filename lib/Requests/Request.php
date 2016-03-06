<?php

namespace SecurePay\XMLAPI\Requests;

use SecurePay\XMLAPI\Exceptions\IncompleteMessageException;
use SecurePay\XMLAPI\Responses\Response;
use SecurePay\XMLAPI\Utils\ApiUtils;

/**
 * The base class of All types of requests.
 *
 * Class Request
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Requests
 */
abstract class Request
{
    /**
     * @var string Should be the merchant Id used for authentication
     */
    protected $merchantId;

    /**
     * @var string Should be the transaction password used for authentication
     */
    protected $txnPassword;

    /**
     * @var bool Should indicate whether this is a payment
     */
    protected $testMode;

    /**
     * @var string The timestamp of when the request object was created.
     */
    protected $timestamp;

    /**
     * Request constructor.
     * @param string $merchantId
     * @param string $txnPassword
     * @param bool $testMode
     */
    protected function __construct($merchantId, $txnPassword, $testMode = false)
    {
        if (strlen($merchantId) != 5 && strlen($merchantId) != 7) {
            throw new \InvalidArgumentException("Merchant ID is neither 5 or 7 digits.");
        }
        if (strlen($txnPassword) < 6 && strlen($txnPassword) > 20) {
            throw new \InvalidArgumentException("Transaction password is not between 6 - 20 characters.");
        }
        $this->merchantId = $merchantId;
        $this->txnPassword = $txnPassword;
        $this->testMode = $testMode;
        $this->timestamp = date("YdmHis000+Z");
    }

    /**
     * Generate and send request to SecurePay XML API servers.
     *
     * @throws IncompleteMessageException When the request message cannot be generated completely.
     * @throws \Exception When CURL is not available.
     */
    public function sendXMLRequest() {
        if (!function_exists("curl_init")) {
            throw new \Exception("Cannot send request as CURL is unavailable.");
        } else {
            if ($this->isReadyToGenerate()) {
                $response = ApiUtils::sendRequest($this->getFullApiUrl(), $this->generateRequestMessage());
                return new Response($response);
            } else {
                throw new IncompleteMessageException("Message is missing some elements. Cannot generate XML message.");
            }
        }
    }

    /**
     * Returns whether test mode is enabled or not.
     *
     * @return bool Test mode is enabled
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * Sets the test mode flag
     *
     * @param bool $testMode Test mode is enabled
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }

    /**
     * Creates an echo request and sends it to SecurePay's XML API server. Requires at least 1 transaction to be added.
     */
    public function sendEchoRequest() {
        Utils::sendRequest($this->getFullApiUrl(), Utils::generateEchoRequest($this->merchantId, $this->txnPassword));
    }

    /**
     * Returns whether the XML request message is ready to be generated.
     *
     * @return bool Whether the request is ready to be generated
     */
    public abstract function isReadyToGenerate();

    /**
     * Returns the full URL to submit the payment request to.
     *
     * @return string The SecurePay URL
     * @throws IncompleteMessageException When attempting to get the payment URL without adding a transaction to the list
     */
    public abstract function getFullApiUrl();

    /**
     * Returns the request type of the XML message. The value of <RequestType>
     *
     * @return string The request type of the XML message
     */
    public abstract function getRequestType();

    /**
     * Generates the complete XML request message that will be sent to SecurePay
     *
     * @return string The XML message
     * @throws IncompleteMessageException When attempting to generate a request message without adding any transactions.
     */
    public abstract function generateRequestMessage();
}