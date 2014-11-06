<?php

class Tools
{

    /**
     * @param string $baseFile
     */
    public static function compilePo2Mo($baseFile)
    {
        $poFile = new PoClass();
        $poFile->readFromFile($baseFile . '.po');
        $poFile->writeMoFile($baseFile . '.mo');
    }

    /**
     * get configuration from ini file
     */
    public static function readConfig($currentDir)
    {
        $configFile =  $currentDir . '/config.ini';
        if(!file_exists($configFile)){
            die(sprintf('Error: %s not found', $configFile));
        }
        return parse_ini_file($configFile);
    }

    /**
     * @param $baseDir
     * @param array $catalogs
     *
     * @return array
     */
    public static function getCatalogs($baseDir, &$catalogs = array())
    {
        $dir = opendir($baseDir);
        while ($item = readdir($dir)){
            if( $item != "." && $item != ".."){
                if( is_dir("$baseDir/$item") ){
                    Tools::getCatalogs("$baseDir/$item", $catalogs);
                } else {
                    if(preg_match('/\/(?<locale>[^\/]*?)\/LC_MESSAGES\/(?<catalog>[^\.]*?)\.po/', "$baseDir/$item", $matches)){
                        $catalogs[$matches['locale']][$matches['catalog']] = $matches['catalog'];
                    }
                }
            }
        }

        return $catalogs;
    }


    /**
     * @param $file
     * @param $isoLocale
     * @param $data
     */
    public static function generatePoFileFromTradukojData($file, $isoLocale, $data)
    {
        $contents = <<<EOD
msgid ""
msgstr ""
"Project-Id-Version: \\n"
"POT-Creation-Date: \\n"
"PO-Revision-Date: \\n"
"Last-Translator: \\n"
"Language-Team: \\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=iso-8859-1\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Language: {$isoLocale}\\n"
"X-Generator: Tradukoj po module 1.0\\n"

EOD;
        ;

        foreach($data as $key=>$message){
            $contents .= <<<EOD

#: here the comment for this translation
msgid "{$key}"
msgstr "{$message}"

EOD;

        }
        file_put_contents($file.".po", $contents);

    }


    /**
     * @param $baseDir
     * @param $config
     * @param bool $uploadFirst
     */
    public static function syncTranslations($baseDir, $config, $uploadFirst = true)
    {
        $server = isset($config['server']) ? $config['server'] : null;
        $port = isset($config['port']) ? $config['port'] : null;
        $clientApi = new ClientSocketService($config);
        $clientApi->init($server, $port);

        $managedLocales = $config['managed_locales'];

        print("\n*** Syncing translations ***\n");

        /**
         * uploading local catalog keys (from local table) to remote server
         */
        if($uploadFirst){

            $catalogs = Tools::getCatalogs($baseDir);
            $data = array();

            foreach($catalogs as $isoLocale=>$catalog){

                $localeParts = explode("_", $isoLocale);
                $locale = $localeParts[0];
                print "\n*processing catalogs of locale $locale\n";

                foreach($catalog as $file){

                    $catalog = $file;
                    $poFile = new PoClass();
                    $fileName = "$baseDir/$isoLocale/LC_MESSAGES/$file.po";
                    $poFile->readFromFile($fileName);
                    $lastDate = filemtime($fileName);
                    print "\tprocessing file $fileName\n";

                    foreach($poFile->getData() as $message){

                        if(!empty($message['msgid'])){
                            //print("\t\t" . $message['msgid'] . ' => ' . $message['msgstr'][0] . "\n");

                            $key = $message['msgid'];
                            $data[$catalog][$key][$locale] = array(
                                'message'   => $message['msgstr'][0],
                                'updatedAt' => date("c", $lastDate),
                                'fileName'  => "$isoLocale/LC_MESSAGES/$file",
                                'bundle'    => $file,
                                'catalog'   => $catalog,
                            );
                        }

                    }
                }
            }
            foreach($data as $catalog=>$info){
                $result = $clientApi->uploadKeys($catalog, $info);
            }
        }

        /**
         * download the remote catalogs and integrate into local .po files
         */

        $result = $clientApi->getCatalogIndex();

        if($result['result']){
            $catalogs = $result['catalogs'];
        }else{
            die('error getting catalogs');
        }

        $masterInfo = array();
        $files = array();

        foreach($catalogs as $catalog){

            print(sprintf("\nProcessing catalog %s ...\n", $catalog));

            $result = $clientApi->downloadKeys($catalog);

            foreach($result['data'] as $key=>$info){

                foreach($info as $locale=>$message){

                    if($message['fileName']){
                        $files[$catalog][$locale] = $message['fileName'];
                    }
                    $masterInfo[$catalog][$locale][$key] = $message['message'];

                }

            }
        }
        if(count($masterInfo)){
            foreach($masterInfo as $catalog=>$info){
                foreach($info as $locale=>$data){
                    $fileName = $files[$catalog][$locale];
                    $file = preg_replace("/\.po$/", "", $fileName);
                    $fileParts = explode("/", $fileName);
                    $isoLocale = $fileParts[0];
                    Tools::generatePoFileFromTradukojData($baseDir . '/' . $fileName, $isoLocale, $data);
                    Tools::compilePo2Mo($baseDir . '/' . $fileName);
                }
            }
        }

        print("\nProcess finished.");
    }



} 