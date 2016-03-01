<?php

namespace SecurePay\XMLAPI\Exceptions;

/**
 * Exception for when attempting to generate an incomplete request.
 *
 * This exception is thrown when attempting to perform an action which requires an action to be added.
 * E.g. If a PaymentRequest does not contain a tansaction, it will throw this error when attempting to generate the payload.
 *
 * Class IncompleteMessageException
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class IncompleteMessageException extends \Exception
{

    /**
     * IncompleteMessageException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}