<?php

namespace SecurePay\XMLAPI\Exceptions;

/**
 * Exception for when a card type is not supported.
 *
 * This exception is thrown when attempting to set card number to a transaction where the card type is not supported.
 * Please see config.ini to see what card types are supported
 *
 * Class CardTypeUnsupportedException
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class CardTypeUnsupportedException extends \Exception
{

    /**
     * CardTypeUnsupportedException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}