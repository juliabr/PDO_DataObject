<?php
/**
 * Validation code (simple version.)
 *
 * For PHP versions  5 and 7
 * 
 * 
 * Copyright (c) 2015 Alan Knowles
 * 
 * This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU Lesser General Public License as   
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * Lesser General Lesser Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *  
 * @category   Database
 * @package    PDO_DataObject
 * @author     Alan Knowles <alan@roojs.com>
 * @copyright  2016 Alan Knowles
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html  LGPL 3
 * @version    1.0
 * @link       https://github.com/roojs/PDO_DataObject
 */
// validate is needed as some constants come from it.... 
require_once 'Validate.php';

 
class PDO_DataObject_Validate
{
    
    // the dataObject being validated..
    private $do;
      
    /**
     * Constructor
     * @param PDO_DataObject $dataobject The object to validate.
     *
     */
    function __construct($dataobject)
    {
        $this->do = $dataobject;
    }
    
   /**
     * validate the values of the object (can be used prior to inserting/updating..)
     *
     * Note: This was always intended as a simple validation routine.
     * It lacks understanding of field length, whether you are inserting or updating (and hence null key values)
     *
     * Usage:
     * if (is_array($ret = $obj->validate())) { ... there are problems with the data ... }
     *
     * Logic:
     *   - defaults to only testing strings/numbers if numbers or strings are the correct type and null values are correct
     *   - validate Column methods : "validate{ROWNAME}()"  are called if they are defined.
     *            These methods should return 
     *                  true = everything ok
     *                  false|object = something is wrong!
     * 
     *   - This method loads and uses the PEAR Validate Class.
     *
     * @requires PEAR Validate.php
     *
     * @access  public
     * @return  array of validation results (where key=>value, value=false|object if it failed) or true (if they all succeeded)
     */
    function validate()
    {
        
        $table = $this->do->tableColumns();
        $ret   = array();
        $seq   = $this->do->sequenceKey();
        
        foreach($table as $key => $val) {
            
            
            // call user defined validation always...
            $method = "Validate" . ucfirst($key);
            if (method_exists($this->do, $method)) {
                $ret[$key] = $this->do->$method();
                continue;
            }
            
            // if not null - and it's not set.......
            
            if ($val & PDO_DataObject::NOTNULL && PDO_DataObject::_is_null($this, $key)) {
                // dont check empty sequence key values..
                if (($key == $seq[0]) && ($seq[1] == true)) {
                    continue;
                }
                $ret[$key] = false;
                continue;
            }
            
            
             if (PDO_DataObject::_is_null($this, $key)) {
                if ($val & PDO_DataObject::NOTNULL) {
                    $this->do->debug("'null' field used for '$key', but it is defined as NOT NULL", 'VALIDATION', 4);
                    $ret[$key] = false;
                    continue;
                }
                continue;
            }

            // ignore things that are not set. ?
           
            if (!isset($this->do->$key)) {
                continue;
            }
            
            // if the string is empty.. assume it is ok..
            if (!is_object($this->do->$key) && !is_array($this->do->$key) && !strlen((string) $this->do->$key)) {
                continue;
            }
            
            // dont try and validate cast objects - assume they are problably ok..
            if (is_object($this->do->$key) && is_a($this->do->$key,'PDO_DataObject_Cast')) {
                continue;
            }
            // at this point if you have set something to an object, and it's not expected
            // the Validate will probably break!!... - rightly so! (your design is broken, 
            // so issuing a runtime error like PEAR_Error is probably not appropriate..
            
            switch (true) {
                // todo: date time.....
                case  ($val & PDO_DataObject::STR):
                    $ret[$key] = Validate::string($this->do->$key, VALIDATE_PUNCTUATION . VALIDATE_NAME);
                    continue;
                case  ($val & PDO_DataObject::INT):
                    $ret[$key] = Validate::number($this->do->$key, array('decimal'=>'.'));
                    continue;
            }
        }
        // if any of the results are false or an object (eg. PEAR_Error).. then return the array..
        foreach ($ret as $key => $val) {
            if ($val !== true) {
                return $ret;
            }
        }
        return true; // everything is OK.
    }