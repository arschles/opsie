Opsie
===

Opsie is a command line parser for PHP. It is inspired by Python's optparse library (http://docs.python.org/library/optparse.html), and basic usage is the same:


    $op = new OptionParser();

    $op->add_option("-a", "--long_a", "var_a", "help for a", "A");
    list($options, $args) = $op->parse_args();
    //do some stuff with $options. $args will be blank right now, but will be used for positional arguments in the future


Opsie currently supports basic features (short/long arguments, defaults, booleans, etc...). Plans for the future include:

*  positional arguments
*  types
*  required arguments
*  arbitrary callbacks
*  argument lists
*  conflict handling

This list is long, but I aim to keep Opsie lean and easy to use first, powerful second, and feature rich third.