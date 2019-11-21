<?php

/**
 * Trait InstanceFactory_Trait
 */
Trait InstanceFactory_Trait
{
    private function createInstance(Properties_Interface $createProperties, Properties_Interface $initProperties) {
        $processorInstanceProperty = $createProperties->getPropertyDefault('ProcessorThis', $createProperties, true);
        $processorClassProperty = $createProperties->getPropertyDefault('processor-class', 'ProcessorView', true);
        switch ($processorClassProperty) {
            case 'ProcessorThis' :
                $processorInstance = $processorInstanceProperty;
                break;
            default :
                //    $processorInstance = new $processorClassProperty($processorInitProperties);
                $processorInstance = $processorClassProperty::createInstance($initProperties);
        }
        return $processorInstance;
    }
}