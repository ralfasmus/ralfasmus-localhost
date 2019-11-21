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
    protected function getDynamicPropertySpecial(string $key) {
        $result = 'unDEfineeD';
        switch ($key) {
            case "data-tooltip-html" :
                $text = $this->getPropertyDefault("text");
                $result = str_replace('"', "'", $text);
                break;
        }
        return $result;
    }
}
