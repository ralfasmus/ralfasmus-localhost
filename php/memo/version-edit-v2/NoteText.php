<?php
/**
 * Text Notizen
 *
 * Class NoteText
 */
final class NoteText extends Note
{

    /**
     * @see Properties_Trait::getDynamicProperty()
     *
     * @param string $key
     * @return |null
     */
    protected function getDynamicProperty(string $key) {
        switch ($key) {
            case "data-tooltip-html" :
                $text = $this->getPropertyDefault("text");
                $result = str_replace('"', "'", $text);
                return $result;
                break;
        }
        return parent::getDynamicProperty($key);
    }
    /**
     * @see Note::initialize()
     */
    protected function initialize() : void {
    }
}
