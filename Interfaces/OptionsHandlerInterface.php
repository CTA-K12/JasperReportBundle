<?php

namespace MESD\Jasper\ReportBundle\Interfaces;

use MESD\Jasper\ReportBundle\InputControl\AbstractReportBundleInputControl;

/**
 * Interface that defines the methods required by an options manager
 */
interface OptionsHandlerInterface 
{
    /**
     * Returns a list of options for the requested input control
     *
     * NOTE: The return should be an empty array if no options exist and null if the option is not handled!
     * 
     * @param  AbstractReportBundleInputControl $inputControl The input control to get options for
     * 
     * @return array                                          The array of options
     */
    public function getList(AbstractReportBundleInputControl $inputControl);
}