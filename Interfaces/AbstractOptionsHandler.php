<?php

namespace Mesd\Jasper\ReportBundle\Interfaces;

/**
 * Interface that defines the methods required by an options manager
 */
abstract class AbstractOptionsHandler
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The map of ids to functions that return their option lists
     *
     * @var array
     */
    protected $functionMap;

    /**
     * The maps of ides to functions that return option lists for ajax selectors
     *
     * @var array
     */
    protected $ajaxFunctionMap;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     */
    public function __construct()
    {
        //Register the functions
        $this->functionMap     = $this->registerFunctions();
        $this->ajaxFunctionMap = $this->registerAjaxFunctions();
    }

    //////////////////////////////
    // METHODS TO BE OVERRIDDEN //
    //////////////////////////////

    /**
     * Register the fucntions (meant to overriden by inheriting class)
     *
     * @return array The map of functions keyed by input control id
     */
    protected function registerFunctions()
    {
        // Overwrite in application
        return [];
    }

    /**
     * Register the functions that will be used in an ajax fashion
     *
     * @return array The map of ajax functions keyed by input control id
     */
    protected function registerAjaxFunctions()
    {
        // Overwrite in application
        return [];
    }

    ///////////////////
    // CLASS METHODS //
    ///////////////////

    /**
     * Returns the list of options for a given input control id, or returns null if the option is not supported
     * This is for non-ajax controls only
     *
     * @param  string     $inputControlId The id of the input control to get a list of options for
     *
     * @return array|null                 The array of options or null if the input control is not supported (and will use jasper if fallback mode is in place)
     */
    public function getList($inputControlId)
    {
        if (array_key_exists($inputControlId, $this->functionMap)) {
            return call_user_func([$this, $this->functionMap[$inputControlId]]);
        } elseif (array_key_exists($inputControlId, $this->ajaxFunctionMap)) {
            return [];
        } else {
            return null;
        }
    }

    /**
     * Returns the list of the options for a given ajax selector control id, or returns null if that input control is not supported
     *
     * @param  string     $inputControlId The id of the input control to get a list of options for
     * @param  integer    $limit          How many options to grab at a time
     * @param  integer    $page           The page number (e.g. if we want results 21 - 30 when page size = 10, then this would be 3)
     * @param  string     $search         The search term
     *
     * @return array|null                 The array of options, or null if not supported
     */
    public function getAjaxList(
        $inputControlId,
        $options = []
    ) {
        if (array_key_exists($inputControlId, $this->ajaxFunctionMap)) {
            return call_user_func([$this, $this->ajaxFunctionMap[$inputControlId]], $options);
        } else {
            return null;
        }
    }

    /**
     * Checks whether this option handler supports the given inputControlId in a non ajax fashion
     *
     * @param  string  $inputControlId The control to check
     *
     * @return boolean                 Whether there exists non-ajax support for the control
     */
    public function supportsOption($inputControlId)
    {
        return array_key_exists($inputControlId, $this->functionMap);
    }

    /**
     * Checks whether this option handler supports the given inputControlId in an ajax fashion
     *
     * @param  string  $inputControlId The control to check
     *
     * @return boolean                 Whether there exists ajax support for the control
     */
    public function supportsAjaxOption($inputControlId)
    {
        return array_key_exists($inputControlId, $this->ajaxFunctionMap);
    }
}
