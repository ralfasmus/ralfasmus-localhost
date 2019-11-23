<?php

final class NoteFactory
{
    use InstanceFactory_Trait;

    /**
     * @param Properties_Interface $pnoteInitProperties Properties, die einer neu instantiierten Note Instance initial gesetzt werden.
     * @return mixed
     * @throws Exception
     */
    public function createNote(Properties_Interface $noteInitProperties) : Note_Interface {
        $view = $noteInitProperties->getPropertyDefault('view','NoteDefault', true);
        return $this->getOrCreateInstance(new PropertiesStatic(array('instance-class' => $view)), $noteInitProperties);
    }

}
