<?php

class OptionParserException extends Exception {}

class OptionParser
{
    //mapping from short arg to index in $options
    private $short_arg_lookup = array();
    //mapping from long arg to index in $options
    private $long_arg_lookup = array();
    
    //array of dictionaries describing options
    private $options = array();
    
    private $scriptname;
    
    private $reserved_short = array('-v', '-h');
    private $reserved_long = array('--verbose', '--help');
    
    private $arg_idx = array();
    
    public function __construct()
    {
        $this->scriptname = $GLOBALS['argv'][0];
    }
    
    /**
    * add a new option
    * 
    * @param string $short the short option
    * @param string $long the long option
    * @param string $destvar the name of the variable where the value should be stored
    * @param string $metavar the name of the meta variable 
    * @param array $optional various options to configure this option. possible options:
    * <ul>
    * <li>action: either "store_true" or "store_false", to configure this option as a boolean</li>
    * <li>default: a default value for this option</li>
    * </ul>
    * @return OptionParser an instance of this object. so you can chain add_option calls together.
    */
    public function add_option($short, $long, $destvar, $help, $metavar = null, array $optional = array())//TODO: add $optional and $type
    {
        if(isset($this->reserved_short[$short]))
        {
            throw new OptionParserException("{$short} is reserved for internal use");
        }
        if(isset($this->reserved_long[$long]))
        {
            throw new OptionParserException("{$long} is reserved for internal use");
        }
        
        $arr = array('short'=>$short, 'long'=>$long, 'destvar'=>$destvar, 'help'=>$help, 'metavar'=>$metavar);
        
        if(isset($optional['action']) && ($optional['action'] == 'store_true' || $optional['action'] == 'store_false'))
        {
            $arr['action'] = $optional['action'];
        }
        if(isset($optional['default']))
        {
            $arr['default'] = $optional['default'];
        }
        
        $this->options[] = $arr;
        $idx = count($this->options) - 1;
        
        if(isset($this->short_arg_lookup[$short])) { throw new OptionParserException("[$short] is already in use"); }
        else if(isset($this->long_arg_lookup[$long])) { throw new OptionParserException("[$long] is already in use"); }
        
        $this->short_arg_lookup[$short] = $idx;
        $this->long_arg_lookup[$long] = $idx;
        
        return $this;
    }
        
    /**
    * print out options help
    */
    public function print_help($output = true)
    {
        $s = "usage: {$this->scriptname} [options]\n\n";
        $s .= "-h, --help\t\tshow this help message and quit\n";
        foreach($this->options as $opt)
        {
            $s .= "{$opt['short']}, {$opt['long']}";
            if(!is_null($opt['metavar']))
            {
                $s .= "={$opt['metavar']}";
            }
            $s .= "\t\t{$opt['help']}\n";
        }
        $s .= "\n";
        
        if($output) print $s;
        return $s;
    }
    
    /**
    * parse command line options
    * 
    * @param array 
    * 
    * @return array a list of 2 elements. The first element is a mapping from variable name (specified in the add_option method, above) to its value.
    * The second element is a mapping of position to value for the command line arguments that were not expected (ie: not given in add_option)
    */
    public function parse_args(array $prog_args = null)
    {
        $argv = $GLOBALS['argv'];
        
        $arg_idx = array();
        
        if(is_null($prog_args)) $prog_args = array_slice($argv, 1);

        //populate an index of all cmdline arguments
        $i = 0;
        foreach($prog_args as $arg)
        {
            $arg_idx[$arg] = $i;
            ++$i;
        }

        $options = array();
        $args = array();
        
        if(isset($arg_idx['-h']) || isset($arg_idx['--help']))
        {
            $this->print_help();
            return array(
                array('help'=>true),
                array()
            );
        }
        if(isset($arg_idx['-v']) || isset($arg_idx['--verbose']))
        {
            $options['verbose'] = true;
        }
        
        $expect_arg = true;
        $i = 0;
        foreach($this->options as $opt)
        {
            $expected_short = $opt['short'];
            $expected_long = $opt['long'];
            $destvar = $opt['destvar'];
            
            $action = isset($opt['action']) ? $opt['action'] : null;
            
            $key = null;
            $val = null;
            
            //short argument found
            if(isset($arg_idx[$expected_short]))
            {
                $key = $expected_short;
                $val_idx = $arg_idx[$expected_short] + 1;
                if(isset($prog_args[$val_idx]))
                {
                    $val = $prog_args[$arg_idx[$expected_short] + 1];
                }
            }
            //long argument found
            else if(isset($this->argv_idx[$expected_long]))
            {
                $split = explode('=', $prog_args[$arg_idx[$expected_long]]);
                $key = $split[0];
                if(isset($split[1])) $val = $split[1];
            }
            
            if(is_null($val) && $action == 'store_true')
            {
                $val = true;
            }
            else if(is_null($val) && $action == 'store_false')
            {
                $val = false;
            }
            else if(isset($opts['default']))
            {
                $val = $opts['default'];
            }
            
            $options[$destvar] = $val;
                        
            ++$i;
        }
        
        return array($options, $args);
    }
}