<?php

/**
 * Interface Processor_Interface defines what processor-specific functionality a processor has to provide.
 */
interface Processor_Interface
{
    /**
     * Sets the Instance-Method that shall be executed via @see Processor_Interface::execute().
     * @param string $processorMethod
     */
    public function setProcessorMethod(string $processorMethod) : void;

    /**
     * Sets the Method-Parameter array that shall be used when executing the Processor method
     * via @see Processor_Interface::execute().
     * @param array $processorMethodParameters
     */
    public function setProcessorMethodParameters(array $processorMethodParameters) : void;

    /**
     * Executes the Processors processorMethod with its ProcessorMethodParameters as parameters. Mostly returns
     * an HTML fragment.
     * @return mixed
     */
    public function execute();
}
