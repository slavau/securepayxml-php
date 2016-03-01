<?php

namespace SecurePay\XMLAPI\Exceptions;

use Exception;

/**
 * Exception for when setting an invalid scheduled date
 *
 * This exception is thrown when attempting to set a scheduled payment date that is not in the future.
 *
 * Class InvalidStartDateException
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class InvalidStartDateException extends \Exception
{
    /**
     * InvalidStartDateException constructor.
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}