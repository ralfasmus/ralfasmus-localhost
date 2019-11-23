<?php
/**
 * Default Notizen
 *
 * Class NoteDefault
 */
final class NoteDefault extends Note
{
    /**
     * Diese Properties muessen in einem EDIT Formular usw. explizit im View .html angegeben werden, wenn sie im
     * Formular vorhanden sein sollen.
     * Fuer alle anderen Properties wird das EDIT SHOW usw. html immer hier dynamisch erzeugt.
     */
    const DEFAULT_PROPERTIES = array('id', 'name', 'art', 'view', 'possible-views', 'datetimesaved', 'datetimecreated', 'files');

    /**
     * @see PropertiesDynamic_Trait::getPropertyDynamic()
     *
     * @param string $key
     * @return |null
     */
    protected function getPropertyDynamic(string $key, $default) {
        switch ($key) {
            case "other-properties-edit-html" :
                $result = $this->getPropertiesHtmlEdit();
                break;
            default: $result = parent::getPropertyDynamic($key, $default);
        }
        return $result;
    }

    /**
     * @see getPropertyDynamic()
     *
     * @param string $key
     * @return |null
     */
    private function getPropertiesHtmlEdit(string $name = '', $value='') {
        if($name == '') {
            $html = '';
            foreach($this->getProperties() as $name => $value) {
                if(!in_array($name, static::DEFAULT_PROPERTIES)) {
                    $html .= $this->getPropertiesHtmlEdit($name, $value);
                }
            }
            return $html;
        } else {
            $id = $this->getId();
            $htmlId = "label-$id-$name";
            return '
            <div class="form-group row">
                <label for="' . $htmlId . '" class="col-form-label col-sm-1">' . ucfirst($name) . '</label>
                <div class="col-sm-11">
                    <input name="note-persistent-' . $name . '" type="text" class="form-control" id="' . $htmlId . '" placeholder="...' . $name . '..." value=\'' . $value . '\'>
                </div>
            </div>';
        }
    }
}
