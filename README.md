[![Latest Stable Version](https://poser.pugx.org/jlaso/tradukoj-po-mo-module/v/stable.svg)](https://packagist.org/packages/jlaso/tradukoj-po-mo-module) [![Total Downloads](https://poser.pugx.org/jlaso/tradukoj-po-mo-module/downloads.svg)](https://packagist.org/packages/jlaso/tradukoj-po-mo-module) [![Latest Unstable Version](https://poser.pugx.org/jlaso/tradukoj-po-mo-module/v/unstable.svg)](https://packagist.org/packages/jlaso/tradukoj-po-mo-module) [![License](https://poser.pugx.org/jlaso/tradukoj-po-mo-module/license.svg)](https://packagist.org/packages/jlaso/tradukoj-po-mo-module)


tradukoj-po-mo-module
=====================


This is an alone program that allows to synchronize your po files with tradukoj remote server

First of all, you need

  - Clone this project whenever you want in your computer
  - launch ```composer install```
  - Create an account in [tradukoj.com](https://www.tradukoj.com) and create a project
  - copy the file config.ini.dis as config.ini and copy the data of project into it

Version
----

1.0.2


Installation
--------------

```sh
git clone https://github.com/jlaso/tradukoj-po-mo-module tradukoj-po-mo-sync
cd tradukoj-po-mo-sync
composer install  # to install dependencies
```


You can also add the module by composer.json, adding in require clause:
```
{
    "jlaso/tradukoj-po-mo-module": "*"
}
```


This is the content of the standard config.ini.dis

```
[jlaso_translations_api_access]
project_id = ?
key = ?
secret = ?
url = 'https://www.tradukoj.com/api/'
managed_locales = ?
```

You need to copy this file as config.ini, and substitute the ? symbol by the ones provided by tradukoj.

Invoke the command
------------------

```sh
cd tradukoj-po-mo-sync
php sync.php --help / to obtain help
php sync.php --upload=yes --dir=path-to-your-locale-files
```

Tool to transform csv files to po files
---------------------------------------

```sh
php csv2po.php --input=sample.csv
```

Look inside sample.csv to know the correct format that the tools expects.

Example
-------

In the folder structure there is a example with LOCALE files and a test program in order to check if gettext is installed in the system.

You can check the test program with this command:
```sh
php examples/php/test.php [locale]
```

The output of this command for en locale is:
```
en_GB
testing gettext
general.no_records_found: No records found.
general.one_record_found: Total one record found
general._d_records_found: Total 10 records found
```

License
----

MIT



