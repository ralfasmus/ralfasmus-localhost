<?php

final class NoteFactory
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
    public function createNote(Properties_Interface $noteCreateProperties, Properties_Interface $noteInitProperties) : Note_Interface {
        return  $this->createInstance($noteCreateProperties, $noteInitProperties);
    }

}
