<?php

/**
 * Eine View Instance dient zur Generierung von HTML inklusive der Ersetzung von Platzhaltern der Form
 * REPLACED_BY_[NOTE|CONFIG|PROPERTY]_xxx_VALUE
 * Class View
 */
final class ProcessorView extends Processor
{
    protected static $PROCESSOR_METHOD_DEFAULT = 'pmGetHtml';
    /**
     * Cache filename => file inhalt fuer View Template Dateien unter /view/
     * @var array string => string
     */
    static private $viewTemplatesCache = array();

    /**
     * Extracts all REPLACED_BY_xxx_VALUE Placeholders from the given $viewHtml and returns them as an array of the
     * xxx parts.
     *
     * @param string $viewHtml
     * @return array
     */
    private function findPlaceHolders(string $viewHtml) : array
    {
        // #### PLACE_HOLDER_*_VALUE im Template ersetzen
        $regexp = "/REPLACED_BY_(((?!_VALUE).)*)_VALUE/";
        preg_match_all($regexp, $viewHtml, $matches);
        return (count($matches) > 0) ? $matches[1] : array();
    }
    /**
     * Search for REPLACED_BY_xxx_VALUE in $viewHtml and
     * - create Processors out of the xxx value
     * - replace the REPLACED_BY_xxx_VALUE with the result of executing the Processor-Method with its method parameters
     * - do this recursively inside the so generated HTML fragments.
     *
     * @param string $viewHtml
     * @return string
     */
    private function processView(string $viewHtml) : string {
        $foundPlaceHolders = $this->findPlaceHolders($viewHtml);
        $placeHoldersReplaced = false;
        foreach ($foundPlaceHolders as $foundPlaceHolder) {
            try {
                list($processorCreateProperties, $processorInitProperties) = self::propertiesFromString($foundPlaceHolder);
                $processorInitProperties->setProperty($this->getView(), 'ParentView');
                $processorInitProperties->setProperty($this->pmGetCssClasses(), 'ParentCssClasses');
                $processorCreateProperties->setProperty( $this,'ProcessorThis');
                $processorPlaceHolderHtml = ProcessorFactory::getSingleInstance()->createProcessor($processorCreateProperties, $processorInitProperties)->execute();
                if(is_array($processorPlaceHolderHtml)) {
                    $processorPlaceHolderHtml = "";
                }
                $viewHtml = str_replace("REPLACED_BY_${foundPlaceHolder}_VALUE", $processorPlaceHolderHtml, $viewHtml);
            } catch (Throwable $throwable) {
                MyThrowable::handleThrowable($throwable, "Fehler beim Bearbeiten von View :" . $this->getView() . ": Placeholder :$foundPlaceHolder:");
                /*
                MyThrowable::handleThrowable($throwable, "Fehler beim Bearbeiten von View :" . $this->getView() . ": Placeholder :$foundPlaceHolder: Davon Properties: "
                        . '<pre>' . var_export($processorCreateProperties, true) . '</pre>'
                        . '<pre>' . var_export($processorInitProperties, true) . '</pre>', true);
                */
            }
            $placeHoldersReplaced = true;
        }
        if($placeHoldersReplaced) {
            /**
             * Rekursion: alle Platzhalter sind ersetzt. Es koennte aber rein theoretisch sein, dass im eingefuegten HTML Platzhalter
             * enthalten sind. Damit diese auch ersetzt werden, jetzt noch einmal aufrufen, aber eben nur, wenn Platzhalter
             * gefunden und ersetzt wurden.
             */
            //$this->processView($viewHtml);
        }
        return $viewHtml;
    }


    /**
     * Liefert alle CSS Klassen fuer diesen View.
     * Wird als @see Processor_Trait::$processorMethod ausgefuehrt.
     *
     * @return string
     */
    public function pmGetCssClasses() : string {
        return $this->getPropertyDefault('ParentCssClasses', '') . ' dvz-' . $this->getView();
    }

    /**
     * Liefert das HTML fuer diesen View. Alle PLACE_HOLDER werden ersetzt.
     * @return string
     * @throws Exception
     */
    public function pmGetHtml() : string {
        $view = $this->getView();
        if (!isset(static::$viewTemplatesCache[$view])) {
            static::$viewTemplatesCache["$view"] = $this->getViewTemplateHtml("${view}.html");
        }
        return $this->processView(static::$viewTemplatesCache[$view]);
    }

    /**
     * Returns the complete name of the view file name in /view/* but without the trailing ".html".
     *
     * @return string
     */
    private function getView() : string {
        $myView = $this->getPropertyMandatory('view', '', false);
        $myParentView = $this->getPropertyDefault('ParentView', '');
        if (substr($myView, 0,1) == '^') {
            $myParentView = '';
        }
        $myParentViewPostfix = ($myParentView != '' ? '-': '');
        $myView = str_replace('^', '', $myView);
        return "${myParentView}${myParentViewPostfix}${myView}";
    }

    /**
     * Laedt einen view vom Filesystem.
     * @param string $viewFilename
     * @return string
     * @throws Exception
     */
    public function getViewTemplateHtml(string $viewFilename): string
    {
        $filename = APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/$viewFilename";
        if (!file_exists($filename)) {
            throw new Exception("Kann View Datei nicht finden: " . str_replace("\\", "/", $filename));
        }
        return file_get_contents($filename);
    }


    /**
     * Transformiert einen (query_string) String in ein Set von Properties. Ersetzt dabei vorher noch ein paar Shortcuts,
     * die in den Views verwendet werden.
     *
     * @param string $processorString Properties, wie sie in einem View Platzhalter oder als Querystring einer URL
     * angegeben werden (aber nicht urlencoded).
     *
     * z.B. pcreate[processor-class]=ProcessorView&pinit[view]=-items_note&pcreate[processor-method]=getHtml
     *
     * @return array(Properties_Interface, Properties_Interface)
     * @throws Exception
     */
    static protected function propertiesFromString(string $processorString) : array {
        assert(is_string($processorString), 'Variable ist kein String:' . var_export($processorString, true));
        $result = array();

        // Shortcuts ersetzen:
        $processorString = str_replace('CONFIG', 'pcreate[processor-class]=ProcessorThis&pcreate[processor-method]=getConfigValue', $processorString);
        $processorString = str_replace('PROPERTY', 'pcreate[processor-class]=ProcessorThis&pcreate[processor-method]=getPropertyDefault', $processorString);
        $processorString = str_replace('~', '&pcreate[pexec][]=', $processorString);

        // als Querystring parsen und daraus ein assoziatives array erzeugen
        parse_str($processorString, $result);
        if(empty($result)) {
            throw new Exception('Kann Processor-Properties from String nicht verarbeiten:'. var_export($processorString));
        }
        $properties = new Properties($result);
        $processorCreateProperties = new Properties($properties->getPropertyDefault('pcreate', array()));
        $processorInitProperties = new Properties($properties->getPropertyDefault('pinit', array()));
        return array($processorCreateProperties, $processorInitProperties);
    }
}
