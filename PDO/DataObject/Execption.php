<?php
/**
 * DataObjects error handler, loaded on demand...
 *
 * PDO_DataObject_Error is a quick wrapper around pear error, so you can distinguish the
 * error code source.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Database
 * @package    PDO_DataObject
 * @author     Alan Knowles <alan@akbkhome.com>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id: Error.php 339237 2016-05-26 03:59:27Z alan_k $
 * @link       http://pear.php.net/package/PDO_DataObject
 */
  
 
class PDO_DataObject_Exception extends Exception
{
    
   
    function __construct($message = '', $type, $previous_exception = null)
    {
        parent::__construct($message, $code, $mode, $level);
        
    }
    
     /**
     * PDO_DataObject_Error factory constructor.
     * @param mixed   $message The text description of the message;
     * @param mixed   $code   DB error code, or string with error message.
     * @param integer $mode   what "error mode" to operate in
     * @param integer $level  what error level to use for $mode & PEAR_ERROR_TRIGGER
     * @param mixed   $debuginfo  additional debug info, such as the last query
     *
     * @access public
     *
     * @see PEAR_Error
     */
    // todo : - support code -> message handling, and translated error messages...
    static function factory($message, $type, $previous_exception)
    {
        $cls = 'PDO_DataObject_Exception_'. $type;
        $code = $previous_exception ? $previous_exception->getCode() : 0;
        return new $cls($message, $code, $previous_exception );
    }
    
    
    
    
    
    
    
}

// child classes - so you can catch them..
class PDO_DataObject_Exception_InvalidArgs extends   PDO_DataObject_Exception {};
class PDO_DataObject_Exception_NoData extends   PDO_DataObject_Exception {};
class PDO_DataObject_Exception_InvalidConfig extends   PDO_DataObject_Exception {};
class PDO_DataObject_Exception_NoClass extends   PDO_DataObject_Exception {};
class PDO_DataObject_Exception_Set extends   PDO_DataObject_Exception {};

