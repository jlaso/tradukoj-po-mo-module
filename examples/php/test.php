<?php

$currentDir = dirname(__FILE__);

$_REQUEST['lang'] = isset($argv[1]) ? $argv[1] : 'en';

require_once($currentDir . '/i18n/i18n.php');
require_once($currentDir . '/../../Classes/PoClass.php');
require_once($currentDir . '/../../Classes/Tools.php');

Tools::compilePo2Mo($currentDir . "/i18n/es_ES/LC_MESSAGES/default");

print "testing gettext\n";

print "general.no_records_found: " . _('general.no_records_found') . "\n";
print "general.one_record_found: " . _('general.one_record_found') . "\n";
print "general._d_records_found: " . sprintf(_('general._d_records_found'), 10) . "\n";
print "test.test2: " . html_entity_decode(_('test.test2')) . "\n";

print "\n";