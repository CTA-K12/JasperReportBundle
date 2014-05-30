<?php

namespace MESD\Jasper\ReportBundle\Factories;

use JasperClient\Interfaces\InputControlAbstractFactory;

use MESD\Jasper\ReportBundle\Interfaces\AbstractOptionsHandler;

class InputControlFactory implements InputControlAbstractFactory
{
    ///////////////
    // CONSTANTS //
    ///////////////


    const DEFAULT_GET_IC_FROM = 'Fallback';
    const DEFAULT_INPUT_CONTROL_TYPE_NAMESPACE = 'MESD\Jasper\ReportBundle\InputControl\\';


    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * Where to get the options list from for an input
     * @var string
     */
    private $getICFrom;

    /**
     * The options handler
     * @var MESD\Jasper\ReportBundle\Interfaces\OptionsHandlerInterface
     */
    private $optionsHandler;

    /**
     * Namespace to get input control type classes from
     * @var string
     */
    private $inputControlTypeNamespace;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param AbstractOptionsHandler  $optionsHandler            The options handler
     * @param string                  $getICFrom                 String flag on where to get options from
     * @param string                  $inputControlTypeNamespace The namespace to get the input control classes from
     */
    public function __construct(
            AbstractOptionsHandler $optionsHandler,
            $getICFrom = null, 
            $inputControlTypeNamespace = null
        ) {
        //Set stuff
        $this->optionsHandler = $optionsHandler;
        $this->getICFrom = $getICFrom ?: DEFAULT_GET_IC_FROM;
        $this->inputControlTypeNamespace = $inputControlTypeNamespace ?: DEFAULT_INPUT_CONTROL_TYPE_NAMESPACE;
    }


    /////////////////////////
    // IMPLEMENTED METHODS //
    /////////////////////////


    /**
     * Processes the XML return from the getInputControls call in the client
     * and constructs the collection of input controls as needed
     * 
     * @param  SimpleXMLElement $specification XML detailing the reports input controls
     * 
     * @return array                           Collection of the reports input controls 
     */
    public function processInputControlSpecification(\SimpleXMLElement $specification) {
        $collection = array();
        //Create the input type class for each element
        foreach($specification->inputControl as $key => $value) {
            //Get the class of the type where the class name is the input type in the specified namespace
            $inputClass = $this->inputControlTypeNamespace . ucfirst($value->type);
            //Try to init the object 
            try {
                $collection[] = new $inputClass(
                    (string)$value->id,
                    (string)$value->label,
                    (string)$value->mandatory,
                    (string)$value->readOnly,
                    (string)$value->type,
                    (string)$value->uri,
                    (string)$value->visible,
                    (object)$value->state,
                    $this->getICFrom,
                    $this->optionsHandler
                );
            } catch (\Exception $e) {
                //Missing an input type class
                throw $e;
            }
        }

        //Return the collection
        return $collection;
    }
}