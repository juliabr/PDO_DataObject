--TEST--
config - class_prefix Test  
--FILE--
<?php
require_once 'includes/init.php';

 

PDO_DataObject::config(array(
        'class_location' =>
                    __DIR__.'/includes/sample_classes/DataObjects_' .
                    PATH_SEPARATOR .
                    __DIR__.'/includes/sample_classes/',
        'class_prefix' => 'DataObjects_' . PATH_SEPARATOR . PATH_SEPARATOR . 'LongPrefix_DataObjects_,
        
        'database' => '',
        'databases' => array(
             'inserttest' => 'mysql://user:pass@localhost/inserttest',
        ),
        
          
     
));

PDO_DataObject::debugLevel(1);
 

echo "\n\n--------\n";
echo "load standard\n" ;


echo get_class(PDO_DataObject::factory('Events')) . "\n";
echo get_class(PDO_DataObject::factory('Dummyshort')) . "\n";
echo get_class(PDO_DataObject::factory('Dummylong')) . "\n";
    


?>
--EXPECT--
