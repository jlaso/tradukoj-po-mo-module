<?php



class ConsoleArgs
{

    private $args;
    private $options;

    function __construct($args, $validArgs = array(), $validOpts = array())
    {
        $this->options = array();
        $this->args = array();

        foreach($validOpts as $option){
            $this->options[$option] = false;
        }
        foreach($validArgs as $arg){
            $this->args[$arg] = null;
        }

        foreach($args as $index=>$arg){

            if($index>0){    // ignore first argument that is the name of the executable file

                if(preg_match("/^--(?<arg>.*)$/", $arg, $matches)){

                    $result = explode("=", $matches['arg']);
                    if(!isset($result[1])){
                        list($key) = $result;
                        if(!array_key_exists($key, $this->args)){
                            die("argument $key not recognized!\n\tUse: ".implode(",", $validArgs));
                        }
                        $this->args[$key] = true;
                    }else{
                        list($key, $value) = $result;
                        if(!array_key_exists($key,$this->options)){
                            die("option $key not recognized!\n\tUse: ".implode(",", $validOpts));
                        }
                        $this->options[$key] = $value;
                    }

                }else{

                    die("argument $arg not recognized");

                }

            }
        }
    }

    function isArgument($arg)
    {
        return array_key_exists($arg, $this->args);
    }

    function getArgument($arg)
    {
        return $this->isArgument($arg) ? $this->args[$arg] : null;

    }

    function isOption($option)
    {
        return array_key_exists($option, $this->options);
    }


    function getOption($option)
    {
        return $this->isOption($option) ? $this->options[$option] : null;
    }

    public function __get($name)
    {
        if(preg_match("/^has(?<option>.*)$/", $name, $matches)){
            return $this->getArgument(lcfirst($matches['option']));
        }
        if(preg_match("/^get(?<argument>.*)$/", $name, $matches)){
            return $this->getOption(lcfirst($matches['argument']));
        }

        return null;
    }

}