<?php

namespace MESD\Jasper\ReportBundle\Helper;

use MESD\Jasper\ReportBundle\Helper\PageLinkManager;

/**
 * Manages the page links
 */
class PageLinkManager
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const DEFAULT_DELIMITER = ' ';

    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The list of page links
     * @var \SplDoublyLinkedList
     */
    private $links;

    /**
     * Sets the delimiter used when printing
     * @var string
     */
    private $delimiter;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     */
    public function __construct() {
        //Init stuff
        $this->links = new \SplDoublyLinkedList();
        $this->delimiter = self::DEFAULT_DELIMITER;
    }


    /**
     * To String
     *   takes each link in the list and combines them seperated by the delimiter
     *
     * @return string The links seperated by the delimited
     */
    public function __toString() {
        //Get the array in FIFO mode
        $pieces = $this->toArray();

        //Convert each piece to a string
        foreach($pieces as $piece) {
            $piece = $piece->__toString();
        }

        //implode with the delimiter and return the result
        return implode($this->delimiter, $pieces);
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Generates an array of page link objects using a url template and the current page number and stores them in this object
     *
     * @param  string $url         The url with a '!page!' substring to replace with the page number
     * @param  int    $currentPage The current page
     * @param  int    $lastPage    The last page of the report
     * @param  array  $options     The options array
     *                               idTemplate: string with a '!page!' substring to use to replace with the page number
     *                                             if no id template is given, id attributes are NOT added to the tags
     *                               classes: array of classes to add to the classes atrribute
     *                               currentPageClass: class name to give to the tag that holds the current class
     *
     * @return self
     */
    public function generatePageLinks($url, $currentPage, $lastPage, $options = []) {
        //Handle the options array
        $idTemplate = (isset($options['idTemplate']) && null != $options['idTemplate']) ? $options['idTemplate'] : null;
        $classes = (isset($options['classes']) && null != $options['classes']) ? $options['classes'] : array();
        $currentPageClass = (isset($options['currentPageClass']) && null != $options['currentPageClass']) ? $options['currentPageClass'] : '';

        //Generates links in the following pattern [1] 2 3 10 ... last
        // 1 ... 5 14 [15] 16 25 ... last
        $this->links->push($this->createPageLink($url, 1, $currentPage, $idTemplate, $classes, $currentPageClass));
        if ($currentPage - 2 > 1) {
            $firstBreak = new PageLink('...');
            $firstBreak->setDisabled(true);
            $this->links->push($firstBreak);
        }
        if ($currentPage - 10 > 1) {
            $this->links->push($this->createPageLink($url, $currentPage - 10, $currentPage, $idTemplate, $classes, $currentPageClass));
        }
        if ($currentPage - 1 > 1) {
            $this->links->push($this->createPageLink($url, $currentPage - 1, $currentPage, $idTemplate, $classes, $currentPageClass));
        }
        if ($currentPage > 1) {
            $this->links->push($this->createPageLink($url, $currentPage, $currentPage, $idTemplate, $classes, $currentPageClass));
        }
        if ($currentPage + 1 < $lastPage) {
            $this->links->push($this->createPageLink($url, $currentPage + 1, $currentPage, $idTemplate, $classes, $currentPageClass));
        }
        if ($currentPage + 10 < $lastPage) {
            $this->links->push($this->createPageLink($url, $currentPage + 10, $currentPage, $idTemplate, $classes, $currentPageClass));
        }
        if ($currentPage + 2 < $lastPage) {
            $lastBreak = new PageLink('...');
            $lastBreak->setDisabled(true);
            $this->links->push($lastBreak);
        }
        if ($currentPage < $lastPage) {
            $this->links->push($this->createPageLink($url, $lastPage, $currentPage, $idTemplate, $classes, $currentPageClass));
        }

        //return self
        return $this;
    }


    /**
     * Helper function for the generate Page links that combines the parameters and creates a new page link
     *
     * @param  string $url              Url Template from generate page links
     * @param  int    $page             The page number to create a page link for
     * @param  int    $currentPage      The current page being viewed
     * @param  string $idTemplate       Optional id template from the generate page links
     * @param  array $classes           Optional array of classes
     * @param  string $currentPageClass Optional currentPageClass
     *
     * @return PageLink                 New page link object
     */
    protected function createPageLink($url, $page, $currentPage, $idTemplate = null, $classes = [], $currentPageClass = '') {
        //If this is the current page add the current page class to the classes array
        if ($page == $currentPage) {
            $classes[] = $currentPageClass;
            $disabled = true;
        } else {
            $disabled = false;
        }

        //Create the page url
        $pageUrl = str_replace('!page!', (string)$page, $url);

        //If an id template is present, create the id
        if ($idTemplate) {
            $id = str_replace('!page!', (string)$page, $idTemplate);
        } else {
            $id = null;
        }

        //instance and return the new page link object
        return new PageLink((string)$page, $pageUrl, $id, $classes, $disabled);
    }


    /**
     * Prints the links that this manager contains seperated by some delimiter
     *
     * @param  string $delimiter Optional delimiter, to override the set delimiter of this object
     *
     * @return string            The page links seperated by the delimiter
     */
    public function printLinks($delimiter = null) {
        //If a temporary delimiter is given
        if ($delimiter) {
            //Temporarily set the delimiter to the passed in argument
            $temp = $this->delimiter;
            $this->delimiter = $delimiter;

            //Call the toString with the swapped delimtier
            $return = $this->__toString();

            //Reset the delimiter
            $this->delimiter = $temp;

            //return the result string
            return $return;
        } else {
            //Else, just call to string
            return $this->__toString();
        }
    }


    /**
     * Returns the number of page links the manager holds
     *
     * @return int The number of page links in the list
     */
    public function count() {
        return $this->links->count();
    }


    /**
     * Converts the data in the manager to an array using the FIFO iterator mode
     *
     * @return array Array of page links
     */
    public function toArray() {
        $return = array();
        $this->links->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);
        for($this->links->rewind(); $this->links->valid(); $this->links->next()) {
            $return[] = $this->links->current();
        }
        return $return;
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Return the array of page links
     *
     * @return \SplDoublyLinkedList Page links
     */
    public function getPageLinks() {
        return $this->links;
    }


    /**
     * Inserts a new page link into the list
     *
     * @param  PageLink $link  The link to insert
     * @param  int      $index Optional index to insert the link at (if not the link will be added at the end)
     *
     * @return int      The new size
     */
    public function addPageLink(PageLink $link, $index = null) {
        if ($index) {
            $this->links->add($index, $link);
        } else {
            $this->links->push($link);
        }
        return $this->links->count();
    }


    /**
     * Removes a page link from the list 
     *
     * @param  int      $index The index to remove, if not given, will remove the link from the end
     *
     * @return PageLink        The link being removed
     */
    public function removePageLink($index = null) {
        if ($index) {
            $return = $this->links->offsetGet($index);
            $this->links->offsetUnset($index);
            return $return;
        } else {
            return $this->links->pop();
        }
    }


    /**
     * Gets the delimiter used when printing
     *
     * @return string The delimiter
     */
    public function getDelimiter() {
        return $this->delimiter;
    }


    /**
     * Sets the delimiter used when printing
     *
     * @param  string $delimiter The delimiter
     *
     * @return self
     */
    public function setDelimiter($delimiter) {
        $this->delimiter = $delimiter;

        return $this;
    }
}