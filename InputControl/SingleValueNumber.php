<?php

namespace MESD\Jasper\ReportBundle\InputControl;

use Symfony\Component\Form\FormBuilder;

/**
 * Single Select
 */
class SingleValueNumber extends AbstractReportBundleInputControl
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The default value
     * @var string
     */
    protected $defaultValue;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     * @param string                  $id        Input Control Id
     * @param string                  $label     Input Controls Label
     * @param string                  $mandatory Whether an input control is mandatory
     * @param string                  $readOnly  Whether an input control is read only
     * @param string                  $type      Input Control Type
     * @param string                  $uri       Uri of the input control on the report server
     * @param string                  $visible   Whether an input control is visible
     * @param object                  $state     State of the input control
     * @param string                  $getICFrom How to handle getting the options
     * @param OptionsHandlerInterface $optionsHandler Symfony Security Context
     */
    public function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionsHandler) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionsHandler);
        $this->defaultValue = ($state->value && null != $state->value ? $state->value : null);
    }


    ///////////////////////
    // IMPLEMENT METHODS //
    ///////////////////////


    /**
     * Attaches this input control to the form builder
     *
     * @param  FormBuilder $formBuilder The form builder object to attach this input control to
     */
    public function attachInputToFormBuilder(FormBuilder $formBuilder) {
        //Add a new number field
        $formBuilder->add($this->id, 'number', array(
                'label'     => $this->label,
                'data'      => $this->defaultValue,
                'required'  => $this->mandatory,
                'read_only' => !$this->readOnly
            )
        );
    }


    ////////////////////
    // CLASS METHODS  //
    ////////////////////


    /**
     * Get the default value
     * @return array The default value
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }
}