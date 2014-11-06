#!/usr/bin/env php
<?php

$currentDir = dirname(__FILE__);

require_once($currentDir . '/Classes/PoClass.php');
require_once($currentDir . '/Classes/ClientSocketService.php');
require_once($currentDir . '/Classes/ConsoleArgs.php');
require_once($currentDir . '/Classes/Tools.php');

$config = Tools::readConfig($currentDir);

$args = new ConsoleArgs($argv, array('help'), array('upload', 'dir'));

if(!($baseDir = $args->getDir) || $args->hasHelp){
    print <<<EOD
Please, use this arguments to invoke this command:

    --help       \tto see this help
    --upload=yes \tto force the inclusion of local translations files to the remote catalog
    --dir=dirname\tthe folder where are the root of LOCALE files

EOD;
    exit();
}

$baseDir = preg_replace("/^\.\//", $currentDir . '/', $baseDir);
print "\nstarting command on {$baseDir}\n\n";

Tools::syncTranslations($baseDir, $config, "yes" == $args->getUpload);
