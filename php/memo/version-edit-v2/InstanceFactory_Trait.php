<?php

/**
 * Trait InstanceFactory_Trait
 */
Trait InstanceFactory_Trait
{
    use SingleInstance_Trait;

    private function createInstance(Properties_Interface $createProperties, Properties_Interface $initProperties) {
        $processorInstanceProperty = $createProperties->getPropertyDefault('ProcessorThis', $createProperties, true);
        $processorClassProperty = $createProperties->getPropertyDefault('instance-class', '', true);
        switch ($processorClassProperty) {
            case 'ProcessorThis' :
                $processorInstance = $processorInstanceProperty;
                break;
            default :
                //    $processorInstance = new $processorClassProperty($processorInitProperties);
                assert(is_callable("$processorClassProperty::createInstance"), 'Nicht aufrufbar.');
                $instanceCreateMethod='createInstance';
                $processorInstance = $processorClassProperty::$instanceCreateMethod($initProperties);
        }
        return $processorInstance;
    }
}