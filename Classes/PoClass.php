<?php

/**
 * Class PoClass
 */
class PoClass
{
    protected $data;
    
    public function getData()
    {
        return $this->data;
    }

    /**
     * Clean new lines and add escapes dollar symbol
     *
     * @param $obj
     *
     * @return array|mixed|string
     */
    protected function clean($obj)
    {
        if (is_array($obj)) {
            foreach ($obj as $key=>$value) {
                $obj[$key] = $this->clean($value);
            }
        } else {
            if ('"' == $obj[0]){
                $obj = substr($obj, 1, -1);
            }
            $obj = str_replace(array("\"\n\"",'$'), array('','\\$'), $obj);
        }

        return $obj;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function readFromFile($file)
    {
        $data = array ();
        
        $handle = fopen($file, 'r');
        if ($handle === false) {
            return false;
        }

        $temp = array();
        $state = null;
        $fuzzy = false;

        // main loop
        while(($line = fgets($handle, 65536)) !== false) {
            $line = trim($line);
//            if ('' === $line){
//                //continue;
//                $data[] = $temp;
//                $temp = array();
//            }

//            print("line => $line \n");

            $tmp1 = preg_split('/\s/', $line, 2);

            if(count($tmp1) > 1){
                list ($key, $_data) = $tmp1;

                switch ($key) {

                    case '#,' : $fuzzy = in_array('fuzzy', preg_split('/,\s*/', $_data));
                    case '#'  : // comments of translator
                    case '#.' : // comments extracted
                    case '#:' : // reference
                    case '#|' : // msgid previous-untranslated-string
                        if (sizeof($temp) && array_key_exists('msgid', $temp) && array_key_exists('msgstr', $temp)) {
                            if (!$fuzzy){
                                $data[] = $temp;
                                //print "1\n";
                            }
                            $temp = array();
                            $state = null;
                            $fuzzy = false;
                        }
                        break;

                    case 'msgctxt':
                    case 'msgid':
                    case 'msgid_plural':
                        $state = $key;
                        $temp[$state] = $_data;
                        break;

                    case 'msgstr' :
                        $state = 'msgstr';
                        $temp[$state][] = $_data;
                        break;

                    default :
                        if (false !== strpos($key, 'msgstr[')) {
                            $state = 'msgstr';
                            $temp[$state][] = $_data;
                        } else {
                            switch ($state) {
                                case 'msgctxt' :
                                case 'msgid' :
                                case 'msgid_plural' :
                                    $temp[$state] .= "\n" . $line;
                                    break;

                                case 'msgstr' :
                                    $temp[$state][sizeof($temp[$state]) - 1] .= "\n" . $line;
                                    break;

                                default :
                                    fclose($handle);
                                    return false;
                            }
                        }
                        break;
                }

            }

        }
        fclose($handle);

        if ($state == 'msgstr'){
            $data[] = $temp;
        }
        //var_dump($temp);die;

        $temp = $data;
        $data = array ();
        foreach ($temp as $entry) {
            foreach ($entry as & $value) {
                $value = $this->clean($value);
                //$value = _po_clean_helper($value);
                if (false === $value) {
                    return false;
                }
            }
            $data[$entry['msgid']] = $entry;
        }
        //var_dump($data); //die;
        $this->data = $data;

        return true;
    }

    /**
     * @param $file
     */
    public function writeMoFile($file)
    {
        $offsets = array();
        $ids     = '';
        $strings = '';
        $mo      = '';

        $data = $this->data;
        ksort($data, SORT_STRING);

        foreach ($data as $entry) {

            //print_r($entry); print "\n";
            $id = $entry['msgid'];
            if (isset ($entry['msgid_plural'])){
                $id .= "\x00" . $entry['msgid_plural'];
            }
            if (array_key_exists('msgctxt', $entry)){
                $id = $entry['msgctxt'] . "\x04" . $id;
            }
            $str       = implode("\x00", $entry['msgstr']);
            $offsets[] = array(strlen($ids), strlen($id), strlen($strings), strlen($str));
            $ids      .= $id . "\x00";
            $strings  .= $str . "\x00";
        }

        $key_start     = 7 * 4 + sizeof($data) * 4 * 4;
        $value_start   = $key_start + strlen($ids);
        $key_offsets   = array();
        $value_offsets = array();
        foreach ($offsets as $v) {
            list ($o1, $l1, $o2, $l2) = $v;
            $key_offsets[]   = $l1;
            $key_offsets[]   = $o1 + $key_start;
            $value_offsets[] = $l2;
            $value_offsets[] = $o2 + $value_start;
        }
        $offsets = array_merge($key_offsets, $value_offsets);

        $mo .= pack('Iiiiiii', 0x950412de, 0, sizeof($data), 7 * 4, 7 * 4 + sizeof($data) * 8, 0, $key_start);
        foreach ($offsets as $offset){
            $mo .= pack('i', $offset);
        }
        $mo .= $ids;
        $mo .= $strings;

        file_put_contents($file, $mo);
    }


} 