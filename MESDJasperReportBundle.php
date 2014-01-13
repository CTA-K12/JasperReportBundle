<?php

namespace MESD\Jasper\ReportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

class MESDJasperReportBundle extends Bundle
{
    public function registerCommands(Application $application){
        parent::registerCommands($application);
    }
}
