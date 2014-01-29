<?php

namespace MESD\Jasper\ReportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReportEventsListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) {
        //Check that symfony's event dispatcher is in the container
        if (!($container->hasDefinition('event_dispatcher') || $container->hasAlias('event_dispatcher'))) {
            return;
        }

        //Get the event dispatcher's definition
        $edDef = $container->findDefinition('event_dispatcher');

        //Register each service with the mesd.jasperreport.event_listener tag
        foreach($container->findTaggedServiceIds('mesd.jasperreport.event_listener') as $id => $attributes) {
            //Foreach listener tag attached to the service, register the listener in the dispatcher
            foreach($attributes as $attribute) {
                //If the priority level is not set, set it to 0
                if (!isset($attribute['priority'])) {
                    $attribute['priority'] = 0;
                }

                $edDef->addMethodCall('addListenerService', array(
                      $attribute['event']
                    , array($id, $attribute['method'])
                    , $attribute['priority'])
                );
            }
        }
    }
}