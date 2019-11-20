<?php

/**
 * Class Processor defines interfaces of each inheriting processor class and makes sure, that common functionality
 * is implemented uniquely (via traits).
 */
abstract class Processor implements Processor_Interface, Properties_Interface
{
    use Processor_Trait;
    use Properties_Trait;
}
