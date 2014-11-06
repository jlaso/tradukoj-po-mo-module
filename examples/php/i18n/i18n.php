<?php

$langs = array (
  'es' => 'ES',
  'en' => 'GB',
);

$code = isset($_REQUEST['lang'])?$_REQUEST['lang']:'en';

if (isset($langs[$code]))
    $iso_code = $code.'_'.$langs[$code];
else{
    $code = "en";
    $iso_code = 'en_GB';
}

if (isset($_SESSION['lang'])) $_SESSION['lang']=$code;

print "$iso_code\n";

// Set the LANGUAGE environment variable to the desired language
putenv ('LANGUAGE='.$iso_code);
putenv ("LC_ALL=$iso_code");
setlocale(LC_ALL, $iso_code);

// Bind a domain to directory
// Gettext uses domains to know what directories to
// search for translations to messages passed to gettext
bindtextdomain('default', dirname(__FILE__).'/');
//die(dirname(__FILE__));
// Set the current domain that gettext will use
textdomain ('default');

