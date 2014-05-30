<?php

namespace Mesd\Jasper\ReportBundle\Exception;

class JasperNotConnectedException extends \Exception
{
    ////////
    // CONSTANTS
    //////
    
    const DEFAULT_EXCEPTION_MESSAGE = 'Not currently connected to the Jasper Report Server';
    const DEFAULT_EXCEPTION_CODE = 0;

    ////////
    // BASE METHODS
    //////

    /**
     * Constructs a new instance of this class
     * 
     * @param string  $message  Message to throw with the exception, defaults to DEFAULT_EXCEPTION_MESSAGE
     * @param integer $code     Exception code number, defaults to DEFAULT_EXCEPTION_CODE
     * @param string  $previous Reference to previous exception
     */
    public function __construct($message = null, $code = null, \Exception $previous = null) {
        //If no message was passed in set the message to the default
        if (is_null($message)) {
            $message = self::DEFAULT_EXCEPTION_MESSAGE;
        }

        //If no code was passed in set the code to the default
        if (is_null($code)) {
            $code = self::DEFAULT_EXCEPTION_CODE;
        }

        //Call the super class constructor
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the string representation of this class
     * 
     * @return string The string representation of this class
     */
    public function __toString() {
        return __CLASS__ . ': ' . $this->message . '\n';
    }
}
