<?php
/**
 * trait SingleInstance_Trait
 */
trait SingleInstance_Trait
{
    static private $singleInstance = null;

    /**
     * Objekte koennen nicht mehr durch new erzeugt werden.
     * SingleInstance_Trait constructor.
     */
    private function __construct() {
    }

    /**
     * @return mixed
     */
    static public function getSingleInstance() : self {
        assert(!is_null(self::$singleInstance), 'SingleInstance Klasse ' . __CLASS__ . ' wurde noch nicht mittels createSingleInstance() instanziiert. Das muss vor dem ersten Aufruf von getSingleInstance passieren.');
        return self::$singleInstance;
    }

    /**
     * Diese Methode kann/muss neu definiert werden in Klassen, die dieses Trait nutzen wollen, aber deren SingleInstance
     * beim ersten Abruf mittels ::getSingleInstance() bereits initialisiert sein muss (mittels ::create).
     * NACH AUIFRUF DER METHOD KANN DIE INSTANCE MITTELS getSingleInstance abgerufen werden und sollte dann initialisiert werden.
     */
    static private function createSingleInstance() : self {
        assert(is_null(self::$singleInstance), 'SingleInstance Klasse ' . __CLASS__ . ' darf nur einmal instanziiert werden!');
        self::$singleInstance = new self;
        return self::$singleInstance;
    }
}
