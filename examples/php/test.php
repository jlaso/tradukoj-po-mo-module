<?php

$currentDir = dirname(__FILE__);

$_REQUEST['lang'] = isset($argv[1]) ? $argv[1] : 'en';

require_once($currentDir . '/i18n/i18n.php');

print "testing gettext\n";

print "general.no_records_found: " . _('general.no_records_found') . "\n";
print "general.one_record_found: " . _('general.one_record_found') . "\n";
print "general._d_records_found: " . sprintf(_('general._d_records_found'), 10) . "\n";

print "\n";