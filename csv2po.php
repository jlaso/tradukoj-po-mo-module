<?php

/**
 * This file is to port csv translations files (look at sample.csv in this same folder) to a .po format
 */

$currentDir = dirname(__FILE__);

require_once($currentDir . '/vendor/autoload.php');
require_once($currentDir . '/Classes/Tools.php');

$args = new \JLaso\ConsoleArgs\ConsoleArgs($argv, array('help'), array('file', 'output'));

if(!($file = $args->getFile) || $args->hasHelp){
    print <<<EOD
Please, use this arguments to invoke this command:

    --help       \tto see this help
    --file=filename.csv \tthe file to convert
    --output=filename.po\toptional, the name of the file where put the conversion

EOD;
    exit();
}

$input = $args->getFile;

if(!$ouput = $args->getOutput){
    $output = substr( $input, 0, strrpos( $input, "." ) );
}

if(!file_exists($input)){
    die("File {$input} doesn't exist!");
}

$locale = "";
$data = array();

foreach(file($input) as $row){
    $row = str_replace(array("\n","\r","\r\n"), "", $row);
    if(preg_match("/^locale=(?<locale>.*)$/", $row, $matches)){
        $locale = $matches['locale'];
        continue;
    }
    if(substr_count($row, ";")){
        list($key, $msg) = explode(";", $row);
        $data[$key] = $msg;
    }
}

$poFile = Tools::generatePoFileFromTradukojData($output, $locale, $data);

print "Done!, you have your output here: {$poFile}\n";