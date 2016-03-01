<?php

namespace SecurePay\XMLAPI\Exceptions;

/**
 * Exception for when a currency is not supported.
 *
 * This exception is thrown when attempting to set the currency of a request to one that is not supported.
 * Please see config.ini file to find out what curencies are supported.
 *
 * Class CurrencyUnsupportedException
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class CurrencyUnsupportedException extends \Exception
{

    /**
     * CurrencyUnsupportedException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}