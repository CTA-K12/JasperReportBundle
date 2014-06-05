<?php

namespace Mesd\Jasper\ReportBundle\Helper;

use Mesd\Jasper\ReportBundle\Interfaces\AbstractOptionsHandler;

class DefaultOptionsHandler extends AbstractOptionsHandler
{
    /**
     * The default options handler that doesnt really do anything, outside of having it so that
     * an options handler is not part of the initial setup if only the jasper options are being used
     */
    
    /////////////////////////
    // IMPLEMENTED METHODS //
    /////////////////////////


    /**
     * Registers the functions, or in this case, returns an empty array
     *
     * @return array An empty array
     */
    protected function registerFunctions() {
        return array();
    }
}