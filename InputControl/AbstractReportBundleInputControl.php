<?php

namespace MESD\Jasper\ReportBundle\InputControl;

use JasperClient\Client\AbstractInputControl;
use JasperClient\Client\JasperHelper;

use MESD\Jasper\ReportBundle\Interfaces\OptionHandlerInterface;

use Symfony\Component\Form\FormBuilder;

/**
 * Abstract Class to extend the input control classes for the bundle from
 *
 * similar to the input class of the client library but with an added options manager parameter
 */
abstract class AbstractReportBundleInputControl extends AbstractInputControl
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const GET_IC_FROM_CUSTOM   = 'Custom';
    const GET_IC_FROM_FALLBACK = 'Fallback';
    const GET_IC_FROM_JASPER   = 'Jasper';

    const MESSAGE_INVALID_ICFROM = 'Invalid getICFrom option: ';

    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The options manager to get the options list from
     * @var MESD\Jasper\ReportBundle\Interfaces\OptionHandlerInterface
     */
    protected $optionHandler;


    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     * 
     * @param string                  $id        Input Control Id
     * @param string                  $label     Input Controls Label
     * @param string                  $mandatory Whether an input control is mandatory
     * @param string                  $readOnly  Whether an input control is read only
     * @param string                  $type      Input Control Type
     * @param string                  $uri       Uri of the input control on the report server
     * @param string                  $visible   Whether an input control is visible
     * @param object                  $state     State of the input control
     * @param string                  $getICFrom How to handle getting the options
     * @param OptionHandlerInterface $optionHandler Symfony Security Context
     */
    function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionHandler) {
        //Set up the super class
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom);

        //Set stuff
        $this->optionHandler = $optionHandler;
    }


    //////////////////////
    // ABSTRACT METHODS //
    //////////////////////

    /**
     * Convert this field into a symfony form object and attach it the form builder
     *
     * @param  FormBuilder $formBuilder Form Builder object to attach this input control to
     */
    abstract public function attachInputToFormBuilder(FormBuilder $formBuilder);


    ////////////////////////
    // OVERRIDDEN METHODS //
    ////////////////////////


    /**
     * Gets the list of options to display for this input control
     * 
     * @return array Array of input options
     */
    public function createOptionList() {
        //Get the options list
        if (self::GET_IC_FROM_CUSTOM == $this->getICFrom) {
            //If custom, assume that the options handler will full handle it
            $optionList = $this->optionHandler->getList($this);
        } elseif (self::GET_IC_FROM_FALLBACK == $this->getICFrom) {
            //If fallback, check if the options handler returns null (doesnt handle) and then make use of the jasper method
            $optionList = $this->optionHandler->getList($this->getId());
            if (null === $optionList) {
                $optionList = $this->getOptionListFromJasper();
            }
        } elseif (self::GET_IC_FROM_JASPER == $this->getICFrom) {
            //If jasper, get the options from the jasper server
            $optionList = $this->getOptionListFromJasper();
        } else {
            throw new \Exception(self::MESSAGE_INVALID_ICFROM . $this->getICFrom);
        }

        //Return the array of options
        return $optionList;
    }


    /**
     * Gets a list of options for the input control from the jasper server
     * 
     * @return array The array of options
     */
    protected function getOptionListFromJasper() {
        $optionList = array();

        //Get the options from the jasper server
        $inputControlStateArray = JasperHelper::convertInputControlState($this->state);

        foreach ($inputControlStateArray["option"] as $key => $option) {
            //Create an option instance for each option
            $optionList[] = new Option (
                $option["value"],
                $option["label"],
                $option["selected"]
            );
        }

        //Return the options list
        return $optionList;
    }
}