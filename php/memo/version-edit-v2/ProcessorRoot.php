<?php
/**
 * Der vom Request erzeugte Root-Processor. Hat keinen Parent und versteht nur getHtml.
 * Dient nur dazu, die in den GET/POST Parametern angegebenen Processor+Methode aufzurufen.
 *
 * Class ProcessorRoot
 */
class ProcessorRoot extends Processor
{
    use SingleInstance_Trait;

    static public function createProcessor(Properties_Interface $properties) : self {
        return self::createSingleInstance()->initialize(null, $properties);
    }

    /**
     * Erzeuge den Next-Level-Processor aus meinen Properties (den GET- und POST- Properties des Requests).
     * @return string
     */
    public function getHtml() : string {
        return $this->callFromProperties($this);
    }
}
