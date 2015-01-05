<?php

require('vendor/autoload.php');
require_once(__DIR__ . '/src/AlikeColorFinder.php');

$flags = new \donatj\Flags();

$tolerance =& $flags->uint('tolerance', 5, 'Computed Difference Tolerance - default 5');
$help      =& $flags->bool('help', false, 'Displays this message');

try {
	$flags->parse();
	if( $help ) {
		throw new Exception;
	}
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}

$subject = file_get_contents("php://stdin");

$af = new \donatj\AlikeColorFinder\AlikeColorFinder();
$af->ExtractColorInfo($subject, $tolerance);
