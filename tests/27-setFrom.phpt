--TEST--
setFrom / set Test
--FILE--
<?php
require_once 'includes/init.php';

PDO_DataObject::config(array(
        'class_location' => __DIR__.'/includes/sample_classes/DataObjects_',
    // fake db..
   
        'database' => 'mysql://user:pass@localhost/inserttest'
      
));

PDO_DataObject::debugLevel(0);
 

echo "\n\n--------\n";
echo "sqlValue - basic Raw;\n" ;

echo PDO_DataObject::factory('Events')
    ->set(['action' => PDO_DataObject::sqlValue('NOW()') ])
    ->whereToString();

echo "\n\n--------\n";
echo "sqlValue - various values..;\n" ;

echo PDO_DataObject::factory('Dummy')
    ->set([
        'ex_string' => 'aaa',
        'ex_sql' => 'bbb'
    ])
    ->whereToString();

echo "\n\n--------\n";
echo "sqlValue - using formating ..;\n" ;

echo PDO_DataObject::factory('Dummy')
    ->set([
        'a_ex_string' => 'aaa',
        'a_ex_sql' => 'bbb'
    ], 'a_%s')
    ->whereToString();


echo "\n\n--------\n";
echo "sqlValue - skip empty...;\n" ;

echo PDO_DataObject::factory('Dummy')
    ->set([
        'ex_string' => 'aaa',
        'ex_sql' => 'bbb'
    ])
    ->set([
        'ex_string' => 'ccc',
        'ex_sql' => ''
    ], '%s', true)
    ->whereToString();





echo "\n\n----------------------------------------------------------------\n";


PDO_DataObject::debugLevel(0);
echo "\n\n--------\n";
echo "enable_null_values = default = off\n" ;

 
echo "\nsetting string and int to null: " . PDO_DataObject::factory('Dummy')
    ->set([
        'ex_null_string' => null,
        'ex_null_int' => null,
    ])->whereToString(). "\n";

try  {
PDO_DataObject::factory('Dummy')
    ->set([    
        'ex_string' => null
      ]) ; 
    
} catch (PDO_DataObject_Exception_Set $e) {
    echo "\nset got errors as expected: {$e->getMessage()}\n";
}   

echo "\n\n--------\n";
echo "TESTING string NULL - enable_null_valus = default = off\n" ;

    
echo "\nsetting string   to 'NULL' : " . PDO_DataObject::factory('Dummy')
    ->set([
        'ex_string' => 'NULL',
        'ex_null_string' => 'NULL',
    ])->whereToString() . "\n";
    
try {   
echo "\nsetting string and int to 'NULL' : " . PDO_DataObject::factory('Dummy')
    ->set([
        'ex_int' => 'NULL',
        'ex_null_int' => 'NULL',
    ])->whereToString() . "\n";    
} catch (PDO_DataObject_Exception_Set $e) {
    echo "\nset got errors as expected: {$e->getMessage()}\n";
}   



echo "TESTING CAST NULL - enable_null_valus = default = off\n" ;    

try {
echo "\nempty where with real null.: " . PDO_DataObject::factory('Dummy')
    ->set([
       'ex_string' => PDO_DataObject::sqlValue('NULL'),
       'ex_int' => PDO_DataObject::sqlValue('NULL'),
    ])->whereToString();
   
} catch (PDO_DataObject_Exception_Set $e) {
    echo "set got errors as expected: {$e->getMessage()}\n";
}

echo "\nempty where with real null.: " . PDO_DataObject::factory('Dummy')
    ->set([
       'ex_null_string' => PDO_DataObject::sqlValue('NULL'),
       'ex_null_int' => PDO_DataObject::sqlValue('NULL'),
    ])->whereToString();

echo "\n\n--------\n";
echo "TESTING props setting\n" ;

// now setting properties...
$d =  PDO_DataObject::factory('Dummy');
$d->ex_string = null;
$d->ex_int = null;
$d->ex_null_string = null;
$d->ex_null_int = null;
echo "\nusing null props : == {$d->whereToString()} == \n";

$d =  PDO_DataObject::factory('Dummy');
$d->ex_string = "null";
$d->ex_int = "null";
$d->ex_null_string = "null";
$d->ex_null_int = "null";
echo "\nusing null props : == {$d->whereToString()} == \n";

$d =  PDO_DataObject::factory('Dummy');
$d->ex_null_string = PDO_DataObject::sqlValue('NULL');
$d->ex_null_int = PDO_DataObject::sqlValue('NULL');
echo "\nusing null props : == {$d->whereToString()} == \n";

try {
$d =  PDO_DataObject::factory('Dummy');
$d->ex_string = PDO_DataObject::sqlValue('NULL');
$d->whereToString();
} catch (PDO_DataObject_Exception_InvalidArgs $e) {
    echo "set got errors as expected: {$e->getMessage()}\n";
}

try {
$d =  PDO_DataObject::factory('Dummy');
$d->ex_int = PDO_DataObject::sqlValue('NULL');
$d->whereToString();
} catch (PDO_DataObject_Exception_InvalidArgs $e) {
    echo "set got errors as expected: {$e->getMessage()}\n";
}


 



 

?>
--EXPECT--
--------
sqlValue - basic Raw;
__construct==["mysql:dbname=inserttest;host=localhost","user","pass",[]]
setAttribute==[3,2]
 Events.action = NOW()

--------
sqlValue - various values..;
(Dummy.ex_string  = 'aaa') AND (Dummy.ex_sql  = 'bbb')

--------
sqlValue - using formating ..;
(Dummy.ex_string  = 'aaa') AND (Dummy.ex_sql  = 'bbb')

--------
sqlValue - skip empty...;
(Dummy.ex_string  = 'ccc') AND (Dummy.ex_sql  = 'bbb')

----------------------------------------------------------------


--------
enable_null_values = default = off

setting string and int to null: (Dummy.ex_null_string IS NULL) AND (Dummy.ex_null_int IS NULL)

set got errors as expected: Set Errors Returned Values: 
Array
(
    [ex_string] => Error: ex_string : type is NOTNULL -> value is equal null
)



--------
TESTING string NULL - enable_null_valus = default = off

setting string   to 'NULL' : (Dummy.ex_string  = 'NULL') AND (Dummy.ex_null_string  = 'NULL')

set got errors as expected: Set Errors Returned Values: 
Array
(
    [ex_int] => Error: ex_int : type is INT -> Non numeric 'NULL' passed to it
    [ex_null_int] => Error: ex_null_int : type is INT -> Non numeric 'NULL' passed to it
)

TESTING CAST NULL - enable_null_valus = default = off
set got errors as expected: Set Errors Returned Values: 
Array
(
    [ex_int] => setting column ex_int to Null is invalid as it's NOTNULL
    [ex_string] => setting column ex_string to Null is invalid as it's NOTNULL
)


empty where with real null.: (Dummy.ex_null_string IS NULL) AND (Dummy.ex_null_int IS NULL)

--------
TESTING props setting

using null props : ==  == 

using null props : == (Dummy.ex_int = 0) AND (Dummy.ex_string  = 'null') AND (Dummy.ex_null_string  = 'null') AND (Dummy.ex_null_int = 0) == 

using null props : == (Dummy.ex_null_string IS NULL) AND (Dummy.ex_null_int IS NULL) == 
set got errors as expected: Error setting col 'ex_string' to NULL - column is NOT NULL
set got errors as expected: Error setting col 'ex_int' to NULL - column is NOT NULL
