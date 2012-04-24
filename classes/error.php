<?php

abstract class WFErrorHandler {

    public static function suppressError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            default:
                if (defined('JCE_REQUEST')) {
                    return true;
                } else {
                    return false;
                }
                break;
            case E_STRICT:
                return true;
                break;
        }
    }

}

// suppress E_STRICT warnings
set_error_handler(array('WFErrorHandler', 'suppressError'), E_ALL | E_STRICT);
?>