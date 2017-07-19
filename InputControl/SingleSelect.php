<?php

namespace Mesd\Jasper\ReportBundle\InputControl;

use Mesd\Jasper\ReportBundle\FormType\AjaxSelectType;
use Symfony\Component\Form\FormBuilder;

/**
 * Single Select
 */
class SingleSelect extends AbstractReportBundleInputControl
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The options list
     *
     * @var array
     */
    protected $optionList;

    /**
     * The input control gets its options via ajax
     *
     * @var boolean
     */
    protected $isAjax;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     *
     * @param string                  $id             Input Control Id
     * @param string                  $label          Input Controls Label
     * @param string                  $mandatory      Whether an input control is mandatory
     * @param string                  $readOnly       Whether an input control is read only
     * @param string                  $type           Input Control Type
     * @param string                  $uri            Uri of the input control on the report server
     * @param string                  $visible        Whether an input control is visible
     * @param object                  $state          State of the input control
     * @param string                  $getICFrom      How to handle getting the options
     * @param OptionsHandlerInterface $optionsHandler Symfony Security Context
     */
    public function __construct(
        $id,
        $label,
        $mandatory,
        $readOnly,
        $type,
        $uri,
        $visible,
        $state,
        $getICFrom,
        $optionsHandler
    ) {
        parent::__construct($id, $label, $mandatory, $readOnly, $type, $uri, $visible, $state, $getICFrom, $optionsHandler);
        $this->optionList = $this->createOptionList();

        // Check if this selector is to get its options via ajax
        if ($optionsHandler->supportsAjaxOption($id)) {
            $this->isAjax = true;
        } else {
            $this->isAjax = false;
        }
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
    public function attachInputToFormBuilder(
        FormBuilder $formBuilder,
                    $data = null
    ) {
        //Convert the options to an array for the form builder
        $choices  = [];
        $selected = null;
        if ($this->isAjax && $data !== null) {
            if (is_array($data)) {
                foreach ($data as $d) {
                    $choices[$d] = $d;
                }
            } else {
                $choices[$data] = $data;
            }
        } else {
        }

        foreach ($this->optionList as $option) {
            $choices[$option->getId()] = $option->getLabel();
            if ($option->getSelected()) {
                $selected = $option->getId();
            }
        }

        $options = [
            'label'      => $this->label,
            'choices'    => $choices,
            'multiple'   => false,
            'data'       => $selected,
            'required'   => $this->mandatory,
            'read_only'  => $this->readOnly,
            'data_class' => null,
        ];

        //Add a new multi choice field to the builder
        $formBuilder->add(
            $this->id,
            $this->isAjax ? new AjaxSelectType() : 'choice',
            $options
        );
    }

    ////////////////////
    // CLASS METHODS  //
    ////////////////////

    /**
     * Get the generated option list
     *
     * @return array The generated option list
     */
    public function getOptionList()
    {
        return $this->optionList;
    }
}
