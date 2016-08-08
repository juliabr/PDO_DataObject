<?php
/**
 * Object Based Database Query Builder and data store
 *  - Introspection Component.
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
  
  
class PDO_DataObject_Introspection
{
    
    private $dataobject;
    
    /**
     * Constructor
     * @arg DataObject $do   - the dataobject.
     *
     */
    
    function __construct($do)
    {
        $this->dataobject = $do;
        if (!is_a($do, 'PDO_DataObject')) {
            throw new Exception('Constructor called without PDO_DataObject');
        }
    }
    /**
     *
     *
     *
     */
    
    function databaseStructure()
    {
        
         global $_DB_DATAOBJECT;
        
        // Assignment code 
        
        if ($args = func_get_args()) {
        
            if (count($args) == 1) {
                
                // this returns all the tables and their structure..
                if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
                    $this->debug("Loading Generator as databaseStructure called with args",1);
                }
                
                $x = new DB_DataObject;
                $x->_database = $args[0];
                $this->_connect();
                $DB = $_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5];
       
                $tables = $DB->getListOf('tables');
                class_exists('DB_DataObject_Generator') ? '' : 
                    require_once 'DB/DataObject/Generator.php';
                    
                foreach($tables as $table) {
                    $y = new DB_DataObject_Generator;
                    $y->fillTableSchema($x->_database,$table);
                }
                return $_DB_DATAOBJECT['INI'][$x->_database];            
            } else {
        
                $_DB_DATAOBJECT['INI'][$args[0]] = isset($_DB_DATAOBJECT['INI'][$args[0]]) ?
                    $_DB_DATAOBJECT['INI'][$args[0]] + $args[1] : $args[1];
                
                if (isset($args[1])) {
                    $_DB_DATAOBJECT['LINKS'][$args[0]] = isset($_DB_DATAOBJECT['LINKS'][$args[0]]) ?
                        $_DB_DATAOBJECT['LINKS'][$args[0]] + $args[2] : $args[2];
                }
                return true;
            }
            // will not get here....
        }
        
        
        
        if (!$this->_database) {
            $this->_connect();
        }
        
        
        // if this table is already loaded this table..
        if (!empty($_DB_DATAOBJECT['INI'][$this->_database][$this->tableName()])) {
            return true;
        }
        
        // initialize the ini data.. if empt..
        if (empty($_DB_DATAOBJECT['INI'][$this->_database])) {
            $_DB_DATAOBJECT['INI'][$this->_database] = array();
        }
         
        if (empty($_DB_DATAOBJECT['CONFIG'])) {
            DB_DataObject::_loadConfig();
        }
        
        // we do not have the data for this table yet...
        
        // if we are configured to use the proxy..
        
        if ( !empty($_DB_DATAOBJECT['CONFIG']['proxy']) ) {
            if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
                $this->debug("Loading Generator to fetch Schema",1);
            }
            class_exists('DB_DataObject_Generator') ? '' : 
                require_once 'DB/DataObject/Generator.php';
                
            
            $x = new DB_DataObject_Generator;
            $x->fillTableSchema($this->_database,$this->tableName());
            return true;
        }
            
             
       
        
        // if you supply this with arguments, then it will take those
        // as the database and links array...
         
        $schemas = isset($_DB_DATAOBJECT['CONFIG']['schema_location']) ?
            array("{$_DB_DATAOBJECT['CONFIG']['schema_location']}/{$this->_database}.ini") :
            array() ;
                 
        if (isset($_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"])) {
            $schemas = is_array($_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"]) ?
                $_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"] :
                explode(PATH_SEPARATOR,$_DB_DATAOBJECT['CONFIG']["ini_{$this->_database}"]);
        }
                    
         
        $_DB_DATAOBJECT['INI'][$this->_database] = array();
        foreach ($schemas as $ini) {
             if (file_exists($ini) && is_file($ini)) {
                
                $_DB_DATAOBJECT['INI'][$this->_database] = array_merge(
                    $_DB_DATAOBJECT['INI'][$this->_database],
                    parse_ini_file($ini, true)
                );
                    
                if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) { 
                    if (!is_readable ($ini)) {
                        $this->debug("ini file is not readable: $ini","databaseStructure",1);
                    } else {
                        $this->debug("Loaded ini file: $ini","databaseStructure",1);
                    }
                }
            } else {
                if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
                    $this->debug("Missing ini file: $ini","databaseStructure",1);
                }
            }
             
        }
        // are table name lowecased..
        if (!empty($_DB_DATAOBJECT['CONFIG']['portability']) && $_DB_DATAOBJECT['CONFIG']['portability'] & 1) {
            foreach($_DB_DATAOBJECT['INI'][$this->_database] as $k=>$v) {
                // results in duplicate cols.. but not a big issue..
                $_DB_DATAOBJECT['INI'][$this->_database][strtolower($k)] = $v;
            }
        }
        
        
        // now have we loaded the structure.. 
        
        if (!empty($_DB_DATAOBJECT['INI'][$this->_database][$this->tableName()])) {
            return true;
        }
        // - if not try building it..
        if (!empty($_DB_DATAOBJECT['CONFIG']['proxy'])) {
            class_exists('DB_DataObject_Generator') ? '' : 
                require_once 'DB/DataObject/Generator.php';
                
            $x = new DB_DataObject_Generator;
            $x->fillTableSchema($this->_database,$this->tableName());
            // should this fail!!!???
            return true;
        }
        $this->debug("Cant find database schema: {$this->_database}/{$this->tableName()} \n".
                    "in links file data: " . print_r($_DB_DATAOBJECT['INI'],true),"databaseStructure",5);
        // we have to die here!! - it causes chaos if we dont (including looping forever!)
        $this->raiseError( "Unable to load schema for database and table (turn debugging up to 5 for full error message)", DB_DATAOBJECT_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        return false;
        
         
        
    }
    
    
    
}