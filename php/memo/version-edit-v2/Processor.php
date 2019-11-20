<?php

/**
 * Class Processor defines interfaces of each inheriting processor class and makes sure, that common functionality
 * is implemented uniquely (via traits).
 */
abstract class Processor implements Processor_Interface, Properties_Interface
{
    use Processor_Trait;

    /**
     * @var string If there is no parameter &pcreate[processor-method]=xxx given for the Processor creation,
     * use this value xxx=static::$PROCESSOR_METHOD_DEFAULT as a default. This member can be redefined in implementing Processor classes.
     */
    protected static $PROCESSOR_METHOD_DEFAULT = '';

    /**
     * Returns the default processor method to execute.
     * @return string
     */
    private function getDefaultProcessorMethod() : string {
        return static::$PROCESSOR_METHOD_DEFAULT;
    }

}
