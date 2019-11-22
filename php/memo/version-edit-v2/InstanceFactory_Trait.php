<?php

/**
 * Trait InstanceFactory_Trait provides common factory functionality of creating an instance based on properties
 * in $creationProperties an initializing the instance with the given $initProperties.
 */
Trait InstanceFactory_Trait
{
    /**
     * Each factory is a single instance of its factory class
     */
    use SingleInstance_Trait;

    /**
     * @param Properties_Interface $createProperties
     * @param Properties_Interface $initProperties
     * @return mixed
     */
    private function getOrCreateInstance(Properties_Interface $createProperties, Properties_Interface $initProperties) {
        $instanceThis = $createProperties->getPropertyDefault('InstanceThis', $createProperties, true);
        $instanceClass = $createProperties->getPropertyMandatory('instance-class', true, 'Kann Instanz nicht erzeugen, weil instance-class "" ist.');
        $instanceClassMethod = $createProperties->getPropertyDefault('instance-class-method', 'createInstance', true);
        switch ($instanceClass) {
            case 'InstanceThis' :
                $instance = $instanceThis;
                break;
            default :
                assert(is_callable("$instanceClass::$instanceClassMethod"), "Instance Creation failed: [$instanceClass::$instanceClassMethod(...)] nicht aufrufbar.");
                $instance = $instanceClass::$instanceClassMethod($initProperties);
        }
        return $instance;
    }
}