<?php

namespace SecurePay\XMLAPI\Exceptions;

/**
 * Exception for when attempting to create a transaction object which is not supported.
 *
 * This exception is thrown when attempting to create a transaction object which does not exist in the SecurePay\XMLAPI\Requests\Payment\Txn\TxnRequestTypes file.
 * Please visit the file to see all available transaction types.
 *
 * Class TxnTypeNotSupported
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Exceptions
 */
class TxnTypeNotSupported extends \Exception
{
    /**
     * TxnTypeNotSupported constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}