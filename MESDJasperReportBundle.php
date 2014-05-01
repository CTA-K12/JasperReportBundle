<?php

namespace MESD\Jasper\ReportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

use MESD\Jasper\ReportBundle\DependencyInjection\Compiler\ReportEventsListenerPass;

class MESDJasperReportBundle extends Bundle
{
    public function registerCommands(Application $application){
        parent::registerCommands($application);
    }

    public function build(ContainerBuilder $container) {
        parent::build($container);
    }
}
