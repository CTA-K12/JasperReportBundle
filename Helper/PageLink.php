<?php

namespace Mesd\Jasper\ReportBundle\Helper;

/**
 * Simple little helper class to help with displaying the page links for a report viewer
 */
class PageLink
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The links href attribute
     * @var string
     */
    private $href;

    /**
     * The classes that the link belongs too
     * @var array
     */
    private $classes;

    /**
     * The id of the anchor element
     * @var string
     */
    private $id;

    /**
     * Whether the link is active or not
     * @var boolean
     */
    private $disabled;

    /**
     * Text to display
     * @var string
     */
    private $text;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param string  $text The text
     * @param string  $href The href
     * @param string  $id   The id
     */
    public function __construct($text, $href = '#', $id = null, $classes = [], $disabled = false) {
        //Set stuff
        $this->id = $id;
        $this->text = $text;
        $this->href = $href;
        $this->classes = $classes;
        $this->disabled = $disabled;
    }


    /**
     * To String, returns the full anchor tag
     *
     * @return string The anchor tag
     */
    public function __toString() {
        if ($this->disabled) {
            return $this->text;
        } else {
            $return = '<a class="Mesd-jasperreport-page-link ';
            foreach($this->classes as $class) {
                $return = $return . $class . ' ';
            }
            $return = $return . '" ';
            if ($this->id) {
                $return = $return . 'id="' . $this->id . '" ';
            }
            $return = $return . 'href="' . $this->href . '" ';
            $return = $return . '>' . $this->text . '</a>';
            return $return;
        }
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the The links href attribute.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Sets the The links href attribute.
     *
     * @param string $href the href
     *
     * @return self
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Gets the The classes that the link belongs too.
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Sets the The classes that the link belongs too.
     *
     * @param array $classes the classes
     *
     * @return self
     */
    public function setClasses(array $classes)
    {
        $this->classes = $classes;

        return $this;
    }

    /**
     * Gets the The id of the anchor element.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the The id of the anchor element.
     *
     * @param string $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the Whether the link is active or not.
     *
     * @return boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Sets the Whether the link is active or not.
     *
     * @param boolean $disabled the disabled
     *
     * @return self
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Gets the Text to display.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the Text to display.
     *
     * @param string $text the text
     *
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}