<?php
/**
 * trait SingleInstance_Trait
 * Setzt das SingleInstance Pattern um und kann in beliebigen Klassen eingesetzt werden. Es ist erforderlich, die
 * SingleInstance initial mittels @see SingleInstance_Trait::createSingleInstance() explizit zu erzeugen.
 * Dadurch wird sichergestellt, dass eine je nach Klasse erforderliche Initialisierung ihrer SingleInstance vor deren
 * erstem Abruf erfolgt.
 */
trait SingleInstance_Trait
{
    static private $singleInstance = null;

    /**
     * Es kann nur eine Instanz dieser Klasse erzeugt werden. Dies muss explizit durch
     * @see SingleInstance_Trait::createSingleInstance() erfolgen.
     */
    protected function __construct() {
        assert(is_null(static::$singleInstance), 'SingleInstance Klasse ' . __CLASS__ . ' darf nur einmal instanziiert werden!');
    }

    /**
     * Abruf der SingleInstance. Diese muss vorher explizit mittels @see SingleInstance_Trait::createSingleInstance()
     * erzeugt worden sein.
     *
     * @return mixed
     */
    static public function getSingleInstance() : self {
        assert(!is_null(static::$singleInstance), 'SingleInstance Klasse ' . __CLASS__ . ' wurde noch nicht mittels createSingleInstance() instanziiert. Das muss vor dem ersten Aufruf von getSingleInstance passieren.');
        return static::$singleInstance;
    }

    /**
     * Um eine SingleInstance abzurufen, muss diese Methode initial einmal aufgerufen worden sein. Somit ist genau
     * festgelegt, wann eine SingleInstance erzeugt wird.
     * Diese Methode kann/muss neu definiert werden in Klassen, die dieses Trait nutzen wollen, aber deren SingleInstance
     * vor dem ersten Abruf mittels @see SingleInstance_Trait::getSingleInstance() bereits zusaetzlich initialisiert worden sein muss.
     */
    static public function createSingleInstance() : self {
        assert(is_null(static::$singleInstance), 'SingleInstance Klasse ' . static::class . ' wurde bereits mittels createSingleInstance() instanziiert. Das darf nur einmal passieren.');
        static::$singleInstance = new static;
        return static::$singleInstance;
    }
}
