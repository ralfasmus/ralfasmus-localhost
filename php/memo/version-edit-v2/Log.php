<?php

/**
 * Allgemeine Methoden.
 * @author asmusr
 */
class Log {

    private static $logMessages = array(array(),array(),array(),array());

    const LOG_LEVEL_INFO = 3;
    const LOG_LEVEL_ERROR = 1;
    const LOG_LEVEL_OFF = 0;

    /**
     * Logged nach /log/...
     * @param $logLevel ObjectAbstract::LOG_LEVEL_DEBUG | ...
     * @param $text
     */
    private static function doLog($logLevel, $messageOrVariable) {
        $messageBrowser = is_scalar($messageOrVariable) ? $messageOrVariable : json_encode($messageOrVariable, JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_HEX_QUOT);
        $messageLogFile = is_scalar($messageOrVariable) ? $messageOrVariable : var_export($messageOrVariable, true);
        self::$logMessages[$logLevel][] = $messageBrowser;
        if($logLevel <= Conf::get('LOG_LEVEL', self::LOG_LEVEL_OFF)) {
            $logFile = Conf::get("LOG_FILE_NAME_BASE") . "/log.txt";
            file_put_contents($logFile, "$messageLogFile\r\n", FILE_APPEND);
        }
    }

    public static function getConsoleLog() {
        $messages = "";
        foreach(self::$logMessages[1] as $message) {
            $messages .= 'console.error("'. $message . '");';
        }
        foreach(self::$logMessages[3] as $message) {
            $messages .= 'console.info("'. $message . '");';
        }
        return "<script>$messages</script>";
    }

    public static function info($messageOrVariable) {
        self::doLog(3, $messageOrVariable);
    }

    public static function error($messageOrVariable) {
        self::doLog(1, $messageOrVariable);
    }

    static public function throwError(Throwable $throwable) {
        // https://trowski.com/2015/06/24/throwable-exceptions-and-errors-in-php7/
        // $message = $throwable->__toString()
        $first = str_replace("\\", "/", $throwable->getFile()) . ':' . $throwable->getLine() . ':' . $throwable->getMessage() . "\\n";
        $trace = $throwable->getTraceAsString();
        $trace = str_replace(array("\\"), array("/"), $trace);
        $trace = str_replace(array("\r\n", "\n"), array("\\n", "\\n"), $trace);
        Log::error("{$first}{$trace}");
    }
}
