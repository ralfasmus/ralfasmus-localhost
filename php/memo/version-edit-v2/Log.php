<?php
/**
 * Class Log
 */
class Log
{

    private static $logMessages = array(array(), array(), array(), array());

    const LOG_LEVEL_DEBUG = 4;
    const LOG_LEVEL_INFO = 3;
    const LOG_LEVEL_ERROR = 1;
    const LOG_LEVEL_OFF = 0;

    /**
     * Logged nach /log/...
     * @param $text
     */
    private static function doLog($logLevel, $messageOrVariable)
    {
        assert(defined('ROOT_DIR'), 'PHP Konstante ROOT_DIR ist nicht definiert.');
        $messageBrowser = is_scalar($messageOrVariable) ? $messageOrVariable : json_encode($messageOrVariable, JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_HEX_QUOT);
        $messageLogFile = is_scalar($messageOrVariable) ? $messageOrVariable : var_export($messageOrVariable, true);
        self::$logMessages[$logLevel][] = $messageBrowser;
        if ($logLevel <= Request::getSingleInstance()->getPropertyDefault('log_level', self::LOG_LEVEL_OFF, true)) {
            $logFile = ROOT_DIR . "/log/memo/log.txt";
            file_put_contents($logFile, "$messageLogFile\r\n", FILE_APPEND);
        }
    }

    public static function getConsoleLog()
    {
        function addMessages(array $messages, string $consoleCommand): string
        {
            $messagesConsoleHtml = '';
            foreach ($messages as $message) {
                $message = str_replace("\n", " ", str_replace("'", "\'", $message));
                $messagesConsoleHtml .= "console.$consoleCommand('SERVER-LOG: $message');";
            }
            return $messagesConsoleHtml;
        }

        $messagesConsoleHtml = "";
        $messagesConsoleHtml .= addMessages(self::$logMessages[1], 'error');
        $messagesConsoleHtml .= addMessages(self::$logMessages[3], 'info');

        // DEBUG messages auch als info messages an Browser Console senden?
        if (4 <= Request::getSingleInstance()->getPropertyDefault('log_level', self::LOG_LEVEL_OFF, true)) {
            $messagesConsoleHtml .= addMessages(self::$logMessages[4], 'info');
        }
        return "<script>$messagesConsoleHtml</script>";
    }

    public static function logInstanceCreated($instance) {
        self::debug('Created Object: ' . self::objectString($instance));
    }

    public static function objectString($instance) : string {
        return get_class($instance) . '(' . spl_object_hash($instance) . ')';
    }

    /**
     * @return string
     */
    public static function getHtmlLog()
    {
        /**
         * @param array $messages
         * @param string $cssClass
         * @return string
         */
        function addMessages2(array $messages, string $cssClass): string
        {
            $messagesHtml = '';
            foreach ($messages as $message) {
                $message = str_replace("\\n", "<br>", str_replace("'", "\'", $message));
                $messagesHtml .= "SERVER-LOG: $message<br>";
            }
            return "<div class='$cssClass'>$messagesHtml</div>";
        }

        $messagesHtml = "";
        $messagesHtml .= addMessages2(self::$logMessages[1], 'error');
        $messagesHtml .= addMessages2(self::$logMessages[3], 'info');

        // DEBUG messages auch als info messages an Browser Console senden?
        if (4 <= Request::getSingleInstance()->getPropertyDefault('log_level', self::LOG_LEVEL_OFF, true)) {
            $messagesHtml .= addMessages2(self::$logMessages[4], 'info');
        }
        return "<div class='ajaxresult'>$messagesHtml</div>";
    }


    public static function debug($messageOrVariable)
    {
        self::doLog(4, $messageOrVariable);
    }

    public static function info($messageOrVariable)
    {
        self::doLog(3, $messageOrVariable);
    }

    public static function error($messageOrVariable)
    {
        self::doLog(1, $messageOrVariable);
    }

    static public function errorThrown(Throwable $throwable)
    {
        // https://trowski.com/2015/06/24/throwable-exceptions-and-errors-in-php7/
        // $message = $throwable->__toString()
        $first = str_replace("\\", "/", $throwable->getFile()) . ':' . $throwable->getLine() . ':' . $throwable->getMessage() . "\\n";
        $trace = $throwable->getTraceAsString();
        $trace = str_replace(array("\\"), array("/"), $trace);
        $trace = str_replace(array("\r\n", "\n"), array("\\n", "\\n"), $trace);
        Log::error("{$first}{$trace}");
    }
}
