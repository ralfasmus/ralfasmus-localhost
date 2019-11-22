<?php
/**
 * Der vom Request erzeugte Root-Processor. Hat keinen Parent und versteht nur getHtml.
 * Dient nur dazu, die in den GET/POST Parametern angegebenen Processor+Methode aufzurufen.
 *
 * Class ProcessorRoot
 */
final class ProcessorFactory
{
    use InstanceFactory_Trait;

    /**
     * Erzeugt einen Processor aus den $processorCreateProperties und ruft die angegebene Methode auf, um das Ergebnis daraus
     * zurueck zu liefern.
    /**
     * @param Properties_Interface $processorCreateProperties Properties, die die Bestimmung/Erzeugung einer Processor Instance steuern.
     * @param Properties_Interface $processorInitProperties Properties, die einer neu instantiierten Processor Instance initial gesetzt werden.
     * @return mixed
     * @throws Exception
     */
    public function createProcessor(Properties_Interface $processorCreateProperties, Properties_Interface $processorInitProperties) : Processor_Interface {
        $processorInstance = $this->getOrCreateInstance($processorCreateProperties, $processorInitProperties);
        $this->setProcessorMethodAndParameter($processorInstance, $processorCreateProperties);
        return $processorInstance;
    }

    private function setProcessorMethodAndParameter(Processor_Interface $processorInstance, Properties_Interface $processorCreateProperties) : void {
        $processorMethod = $processorCreateProperties->getPropertyDefault('processor-method', 'unDEfineeD', true);
        if($processorMethod != 'unDEfineeD') {
            $processorInstance->setProcessorMethod($processorMethod);
        }
        $processorMethodParameters = $processorCreateProperties->getPropertyDefault('pexec', null, true);
        if(!is_null($processorMethodParameters)) {
            $processorInstance->setProcessorMethodParameters($processorMethodParameters);
        }
    }
}