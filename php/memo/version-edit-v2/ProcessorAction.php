<?php
/**
 * Processor, der ein Response-Fragment direkt liefert, ohne einen View view/*.html zu verarbeiten.
 * Es werden Aktionen im Backend ausgefuehrt.
 *
 * Class ProcessorAction
 */
class ProcessorAction extends Processor
{
    public function __construct(?Processor $parentProcessor, Properties_Interface $properties)
    {
        parent::__construct($parentProcessor, $properties);
    }

    /**
     * Speichere die Note Instance, mit den Daten, die in den Request GET/POST-Daten spezifiziert sind.
     *
     * ?processor-class=ProcessorAction&processor-method=noteSave
     *
     * @return string
     * @throws Exception
     */
    public function noteSave() : string {
        $this->getPersistance()->noteSave($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Loesche die Note Instance, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?processor-class=ProcessorAction&processor-method=noteDelete
     *
     * @return string
     * @throws Exception
     */
    public function noteDelete() : string {
        $this->getPersistance()->noteDelete($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Sichere die Note Instance, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?processor-class=ProcessorAction&processor-method=noteBackup
     *
     * @return string
     * @throws Exception
     */
    public function noteBackup() : string {
        $this->getPersistance()->noteBackup($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Stelle die Note Instance aus dem Delete/Backup wieder her, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?processor-class=ProcessorAction&processor-method=noteRecover
     *
     * @return string
     * @throws Exception
     */
    public function noteRecover() : string {
        throw new Exception('Noch nicht implementiert.');
        $this->getPersistance()->noteRecover($this->getUpdatedActionNote());
        return '';
    }
}
