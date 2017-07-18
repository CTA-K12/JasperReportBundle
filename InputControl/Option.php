<?php

namespace Mesd\Jasper\ReportBundle\InputControl;

/**
 * Option
 */
class Option
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The id of the option
     * @var string
     */
    private $id;

    /**
     * The label of the option
     * @var string
     */
    private $label;

    /**
     * Whether the option is selected or not
     * @var boolean
     */
    private $selected;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     *
     * @param string  $id       The options id
     * @param string  $label    The options label
     * @param boolean $selected Whether the option is selected or not
     */
    public function __construct(
        $id,
        $label,
        $selected = false
    ) {
        //Set stuff
        $this->id       = $id;
        $this->label    = $label;
        $this->selected = $selected;
    }

    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////

    /**
     * Get the id
     * @return string The options id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the label
     * @return string The options label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the selected boolean
     * @return boolean Whether the option is selected or not
     */
    public function getSelected()
    {
        return $this->selected;
    }
}
