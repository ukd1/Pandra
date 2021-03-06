<?php
/**
 * (c) 2010 phpgrease.net
 *
 * @author Michael Pearson <pandra-support@phpgrease.net>
 */

/**
 * Example dynamic Address Book construct using Column Family (Super) and Super Columns
 */

session_start();

error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../config.php');

if (!PandraCore::connect('default', 'localhost')) {
    die(PandraCore::$lastError);
}

// hard code the row key for this example
$keyID = 'PAddressStore';

// default keyspace in storage.conf
$keySpace = 'Keyspace1';

// default Super Column name
$columnFamilyName = 'Super1';

class Address extends PandraSuperColumn {
    public function init() {
        $this->addColumn('city', 'string');
        $this->addColumn('street', 'string');
        $this->addColumn('zip', 'int');
    }
}

class Addresses extends PandraSuperColumnFamily {

    var $keySpace = 'Keyspace1';
    var $name = 'Super1';

    public function init() {
        $this->addSuper(new Address('homeAddress'));
        $this->addSuper(new Address('workAddress'));
    }
}

$addrs = new Addresses();
$addrs->keyID = $keyID;

// home address
$homeAddr = $addrs->getColumn('homeAddress');
$homeAddr->setColumn('city', 'san francisco');
$homeAddr->setColumn('street', '1234 x street');
$homeAddr->setColumn('zip', '94107');

// work address
$workAddr = $addrs->getColumn('workAddress');
$workAddr->setColumn('city', 'san jose');
$workAddr->setColumn('street', '9876 y drive');

// custom labelled supercolumn
$customAddr = new Address('customAddress');
$customAddr->setColumn('city', 'another city');
$addrs->addSuper($customAddr);

if (!$addrs->save()) {
    die($addrs->lastError());
}

unset($addrs);

// Load the saved Addresses 
$addrs = new Addresses();
if (!$addrs->load($keyID)) {
    die($addrs->lastError());
}

// Show our loaded CF in JSON format, including key path
echo '<pre>'.$addrs->toJSON(TRUE).'</pre>';

// Example export -> import of JSON structure from loaded data into new object
$addrsClone = new Addresses();

// Populates data from JSON export (therefore must exclude key path)
$addrsClone->populate($addrs->toJSON());

// Set key
$addrsClone->setKeyID($keyID);

// Show the JSON for our CF with cloned data.  This and the prior echo should match
echo '<pre>'.$addrsClone->toJSON(TRUE).'</pre>';
?>