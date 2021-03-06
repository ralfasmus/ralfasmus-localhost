<?php
/**
 * Processor, der ein Response-Fragment direkt liefert, ohne einen View view/*.html zu verarbeiten.
 * Es werden Aktionen im Backend ausgefuehrt.
 *
 * Class ProcessorAction
 */
final class ProcessorAction extends Processor
{
    /**
     * Speichere die Note Instance, mit den Daten, die in den Request GET/POST-Daten spezifiziert sind.
     *
     * ?pcreate[instance-class]=ProcessorAction&pcreate[processor-method]=noteSave
     *
     * @return string
     * @throws Exception
     */
    public function noteSave() : string {
        $this->getPersistence()->noteSave($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Loesche die Note Instance, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?pcreate[instance-class]=ProcessorAction&pcreate[processor-method]=noteDelete
     *
     * @return string
     * @throws Exception
     */
    public function noteDelete() : string {
        $this->getPersistence()->noteDelete($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Sichere die Note Instance, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?pcreate[instance-class]=ProcessorAction&pcreate[processor-method]=noteBackup
     *
     * @return string
     * @throws Exception
     */
    public function noteBackup() : string {
        $this->getPersistence()->noteBackup($this->getUpdatedActionNote());
        return '';
    }

    /**
     * Stelle die Note Instance aus dem Delete/Backup wieder her, die in den Request GET/POST-Daten spezifiziert ist.
     *
     * ?pcreate[instance-class]=ProcessorAction&pcreate[processor-method]=noteRecover
     *
     * @return string
     * @throws Exception
     */
    public function noteRecover() : string {
        throw new Exception('Noch nicht implementiert.');
        $this->getPersistence()->noteRecover($this->getUpdatedActionNote());
        return '';
    }
}
