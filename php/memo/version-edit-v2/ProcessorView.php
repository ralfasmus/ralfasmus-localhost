<?php

/**
 * Eine View Instance dient zur Generierung von HTML inklusive der Ersetzung von Platzhaltern der Form
 * REPLACED_BY_[NOTE|CONFIG|PROPERTY]_xxx_VALUE
 * Class View
 */
class ProcessorView extends Processor
{

    /**
     * Cache filename => file inhalt fuer View Template Dateien unter /view/
     * @var array string => string
     */
    static private $viewTemplatesCache = array();


    public function __construct(?Processor $parentProcessor, Properties_Interface $properties)
    {
        parent::__construct($parentProcessor, $properties);
    }

    private function findPlaceHolders(string $viewHtml) : array
    {
        // #### PLACE_HOLDER_*_VALUE im Template ersetzen
        $regexp = "/REPLACED_BY_(((?!_VALUE).)*)_VALUE/";
        preg_match_all($regexp, $viewHtml, $matches);
        return (count($matches) > 0) ? $matches[1] : array();
    }
    /**
     * Rekursiv im erzeugten HTML nach REPLACED_BY suchen, das HTML dafuer generieren und diese dann ersetzen
     * @param string $viewHtml
     * @return string
     */
    private function processView(string $viewHtml) : string {
        $foundPlaceHolders = $this->findPlaceHolders($viewHtml);
        $placeHoldersReplaced = false;
        foreach ($foundPlaceHolders as $foundPlaceHolder) {
            try {
                $properties = self::propertiesFromString($foundPlaceHolder);
                $processorPlaceHolderHtml = $this->callFromProperties($properties);
                $viewHtml = str_replace("REPLACED_BY_${foundPlaceHolder}_VALUE", $processorPlaceHolderHtml, $viewHtml);
            } catch (Throwable $throwable) {
                MyThrowable::handleThrowable($throwable, "Fehler beim Bearbeiten von View :" . $this->getView() . ": Placeholder :$foundPlaceHolder: Davon Properties: "
                        . var_export($properties, true), true);
            }
            $placeHoldersReplaced = true;
        }
        if($placeHoldersReplaced) {
            /**
             * alle Platzhalter sind ersetzt. Es koennte aber rein theoretisch sein, dass im eingefuegten HTML Platzhalter
             * enthalten sind. Damit diese auch ersetzt werden, jetzt noch einmal aufrufen, aber eben nur, wenn Platzhalter
             * gefunden und ersetzt wurden.
             */
            //$this->processView($viewHtml);
        }
        return $viewHtml;
    }


    protected function getCssClasses() : string {
        return parent::getCssClasses() . ' dvz-' . $this->getView();
    }


    protected function getView() : string {
        $myView = $this->getPropertyMandatory('view', '', false);
        $myParentView = (is_null($this->getParentProcessor()) || substr($myView, 0,1) == '^')
                ? ''
                : $this->getParentProcessor()->getView();
        $myParentViewPostfix = ($myParentView != '' ? '-': '');
        $myView = str_replace('^', '', $myView);
        return "${myParentView}${myParentViewPostfix}${myView}";
    }

    /**
     * Liefert das HTML fuer diesen View. Alle PLACE_HOLDER werden ersetzt.
     * @return string
     * @throws Exception
     */
    public function getHtml() : string {
        $view = $this->getView();
        if (!isset(self::$viewTemplatesCache[$view])) {
            self::$viewTemplatesCache["$view"] = $this->getViewTemplateHtml("${view}.html");
        }
        return $this->processView(self::$viewTemplatesCache[$view]);
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
     * z.B. processor-class=ProcessorView&processor-class-properties[view]=-items_note&processor-method=getHtml
     *
     * @return Properties_Interface
     * @throws Exception
     */
    static protected function propertiesFromString(string $processorString) : Properties_Interface {
        assert(is_string($processorString), 'Variable ist kein String:' . var_export($processorString, true));
        $result = array();

        // Shortcuts ersetzen:
        $processorString = str_replace('CONFIG', 'processor-class=this&processor-method=getConfigValue', $processorString);
        $processorString = str_replace('PROPERTY', 'processor-class=this&processor-method=getPropertyDefault', $processorString);
        $processorString = str_replace('~', '&pmp[]=', $processorString);

        // als Querystring parsen und daraus ein assoziatives array erzeugen
        parse_str($processorString, $result);
        if(empty($result)) {
            throw new Exception('Kann Processor-Properties from String nicht verarbeiten:'. var_export($processorString));
        }
        return new Properties($result);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getConfigValue(string $key) : string {
        return $this->getRequest()->getConfig()->getConfigValue($key);
    }
}
