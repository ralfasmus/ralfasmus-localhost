<?php
/**
 * Text Notizen
 *
 * Class NoteText
 */
final class NoteText extends Note
{

    /**
     * @see PropertiesDynamic_Trait::getPropertyDynamic()
     *
     * @param string $key
     * @return |null
     */
    protected function getPropertyDynamic(string $key, $default) {
        switch ($key) {
            case "data-tooltip-html" :
                $text = $this->getPropertyDefault("text", '');
                $result = str_replace('"', "'", $text);
                break;
            default: $result = parent::getPropertyDynamic($key, $default);
        }
        return $result;
    }
}
