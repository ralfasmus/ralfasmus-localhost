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

    /**
     * Liefert die CSS Klassen dieses Processors. Da dies der Root Processor ist, sind dies Klassen auf dem #page Element.
     *
     * @return string
     */
    protected function getCssClasses() : string {
        return $this->getCssClassesFromParameters() . (is_null($this->getParentProcessor()) ? '' : $this->getParentProcessor()->getCssClasses());
    }

    /**
     * Es koennen per GET/POST Parameter 'css-page' css Klassen auf dem #page Element gesetzt werden.
     * Bsp: ?css-page=full-screen-editor-on-load -> #page.full-screen-editor-on-load - Auswirkung siehe init.js
     *
     * @return string
     */
    private function getCssClassesFromParameters() : string {
        return $this->getRequest()->getPropertiesRequest()->getPropertyDefault('css-page', ''). ' ';
    }
}
