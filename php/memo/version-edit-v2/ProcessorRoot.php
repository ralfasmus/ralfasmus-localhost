<?php
/**
 * Der vom Request erzeugte Root-Processor. Hat keinen Parent und versteht nur getHtml.
 *
 * Class ProcessorRoot
 */
class ProcessorRoot extends Processor
{
    /**
     * ProcessorRoot constructor.
     * @param Properties_Interface $properties Die GET- und POST- Properties aus dem Request.
     */
    public function __construct(Properties_Interface $properties)
    {
        parent::__construct(null, $properties);
    }

    /**
     * Erzeuge den Next-Level-Processor aus meinen Properties (den GET- und POST- Properties des Requests).
     * @return string
     */
    public function getHtml() : string {
        return $this->callFromProperties($this->properties);
    }
}
