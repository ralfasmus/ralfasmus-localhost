<?php
/**
 * Bis jetzt noch nicht zur Instanziierung genutzt.
 * Class MyThrowable
 */
class MyThrowable // extends Throwable
{
    const STOP_EXECUTION_ON_ERROR = true;
    /**
     * Aufruf macht nur Sinn, wenn $message nicht leer ist.
     *
     * @param Throwable $throwable
     * @param string $message
     * @param bool $throw true: Throwable bubbeln, sonst hier loggen.
     * @throws Exception
     */
    static public function handleThrowable(Throwable $throwable, string $message, bool $throw = false) {
        assert(!is_null($message) && $message != '');
        if($throw || self::STOP_EXECUTION_ON_ERROR) {
            echo "<pre>".  "$message\n" . $throwable->getMessage() . "\n" . $throwable->getTraceAsString() . "</pre>";
            throw new Exception($message, $throwable->getCode(), $throwable);
        } else {
            if($throwable->getPrevious()) {
                self::handleThrowable($throwable->getPrevious(), $throwable->getPrevious()->getMessage());
            }
            Log::error("<strong>$message</strong>");
            Log::errorThrown($throwable);
        }
    }

    /**
     * @param string $message
     * @param Throwable $throwable
     * @throws Exception
     */
    static public function throw(string $message, ?Throwable $throwable = null) {
        if(is_null($throwable)) {
            throw new Exception($message);
        } else {
            throw new Exception($message, $throwable->getCode(), $throwable);
        }
    }
}
