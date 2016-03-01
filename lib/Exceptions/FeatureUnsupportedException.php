<?php

namespace SecurePay\XMLAPI\Exceptions;

/**
 * Exception for when an action is not supported by SecurePay.
 *
 * This exception is thrown when attempting to perform an action that SecurePay cannot handle.
 * E.g. Adding more than 1 transaction to a request.
 *
 * Class FeatureUnsupportedException
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class FeatureUnsupportedException extends \Exception
{

    /**
     * FeatureUnsupportedException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}