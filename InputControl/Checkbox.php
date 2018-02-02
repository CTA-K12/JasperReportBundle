<?php

namespace Mesd\Jasper\ReportBundle\InputControl;

use Symfony\Component\Form\FormBuilder;

/**
 * Checkbox
 */
class Checkbox extends AbstractReportBundleInputControl
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
    public function __construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionsHandler)
    {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionsHandler);
        $this->defaultValue = ($state->value && null != $state->value ? $state->value : null);
    }


    ///////////////////////
    // IMPLEMENT METHODS //
    ///////////////////////


    /**
     * Convert this field into a symfony form object and attach it the form builder
     *
     * @param  FormBuilder $formBuilder Form Builder object to attach this input control to
     * @param  mixed       $data        The data for this input control if available
     */
    public function attachInputToFormBuilder(FormBuilder $formBuilder, $data = null)
    {
        //Add a new text field
        $formBuilder->add(
            $this->id,
            'checkbox',
            array(
                'label'     => $this->label,
                'data'      => filter_var($this->defaultValue, FILTER_VALIDATE_BOOLEAN),
                'required'  => false,
                'read_only' => $this->readOnly,
                'data_class'=> null
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
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
