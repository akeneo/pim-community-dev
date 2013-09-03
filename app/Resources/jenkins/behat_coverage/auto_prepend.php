<?php
require_once 'PHP/CodeCoverage/Autoload.php';

$coverage = new PHP_CodeCoverage;
$filter = $coverage->filter();

$filter->addFileToBlacklist(__FILE__);
$filter->addFileToBlacklist(dirname(__FILE__) . '/auto_append.php');

$filter->addDirectoryToBlacklist(__DIR__ . '/../../../../vendor');
$filter->addDirectoryToBlacklist(__DIR__ . '/../../../../app');
$filter->addDirectoryToWhitelist(__DIR__ . '/../../../../src');

$coverage->setAddUncoveredFilesFromWhitelist(true);
$coverage->setProcessUncoveredFilesFromWhitelist(true);

$coverage->start($_SERVER['SCRIPT_FILENAME']);
