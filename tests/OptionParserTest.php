<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../OptionParser.class.php';

class OptionParserTest extends PHPUnit_Framework_TestCase
{
    private $o = null;
    
    protected function setUp()
    {
        $this->o = new OptionParser();
    }
    
    protected function tearDown()
    {
        $this->o = null;
    }
    
    public function testPrintHelp()
    {
        $this->o->add_option('-a', '--abc', 'b', 'this is some help');
        
        $this->assertTrue(is_string($this->o->print_help(false)));
    }
    
    public function testSimpleCmdlineString()
    {
        $this->o->add_option('-a', '--longa', 'var_a', 'help for a', 'A');
        $this->o->add_option('-b', '--longb', 'var_b', 'help for b', 'B');
        
        list($options, $args) = $this->o->parse_args(array('-a', 'thisisa', '-b', 'thisisb'));
                
        $this->assertTrue(isset($options['var_a']));
        $this->assertTrue(isset($options['var_b']));
        
        $this->assertEquals($options['var_a'], 'thisisa');
        $this->assertEquals($options['var_b'], 'thisisb');
    }
    
    public function testHelp()
    {
        $this->o->add_option('-a', '--longa', 'var_a', 'help for a', 'A');
        
        ob_start();
        list($options, $args) = $this->o->parse_args(array('-h'));
        ob_end_clean();

        $this->assertTrue(isset($options['help']));
        $this->assertEquals($options['help'], true);
    }
    
    public function testMalformedSupersetCmdlineString()
    {
        $this->o->add_option('-a', '--longa', 'var_a', 'help for a', 'A');
        list($options, $args) = $this->o->parse_args(array('-a', 'thisisa', 'b'));
        
        $this->assertTrue(isset($options['var_a']));
        $this->assertEquals($options['var_a'], 'thisisa');
    }
    
    public function testMalformedSubsetCmdlineString()
    {
        $this->o->add_option('-a', '--longa', 'var_a', 'help for a', 'A');
        list($options, $args) = $this->o->parse_args(array('-a'));
        
        $this->assertFalse(isset($options['var_a']));
    }
}
