<?php

namespace MESD\Jasper\ReportBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MESDJasperReportBundle extends Bundle
{
    public function registerCommands(Application $application){
        parent::registerCommands($application);
    }
}
