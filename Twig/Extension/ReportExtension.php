<?php

namespace MESD\Jasper\ReportBundle\Twig\Extension;

use MESD\Jasper\ReportBundle\Helper\DisplayHelper;

class ReportExtension extends \Twig_Extension {

    ////////////////
    // VARIABLES  //
    ////////////////


    /**
     * The Jasper Reports Bundle display helper
     * @var MESD\Jasper\ReportBundle\Helper\DisplayHelper
     */
    private $displayHelper;

    /**
     * The Twig Environment Reference
     * @var [type]
     */
    private $environment;

    /**
     * The default route to handle html page loads
     * @var string
     */
    private $defaultPageRoute;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param DisplayHelper $displayHelper The display helper reference
     */
    public function __construct(DisplayHelper $displayHelper) {
        $this->displayHelper = $displayHelper;
    }


    //////////////////////////////
    // TWIG EXTENSION INTERFACE //
    //////////////////////////////


    //InitRuntime function, called at runtime, overriding to get an instance of the twig environment
    public function initRuntime(\Twig_Environment $environment) {
        $this->environment = $environment;
    }

    //Get functions lists the functions in this class
    public function getFunctions() {
        //Function definition
        return array(
            'mesd_report_render_folder_view'    => new \Twig_Function_Method($this, 'renderFolderView', array('is_safe' => array('html'))),
            'mesd_report_render_report_view'    => new \Twig_Function_Method($this, 'renderReportView', array('is_safe' => array('html'))),
            'mesd_report_render_page_links'     => new \Twig_Function_Method($this, 'renderPageLinks',  array('is_safe' => array('html')))
        );
    }

    //Returns the name of this extension (this is required)
    public function getName() {
        return 'mesd_report_extension';
    }


    ///////////////
    // FUNCTIONS //
    ///////////////


    //Displays the list of contents from the given folder collection object
    //generate tree is a flag to determine wether to make a tree from root or just show its contents
    //maxDepth is how many folders deep to display, 0 will display whole tree under the root, 1 will only display the contents of root
    public function renderFolderView($folderView, $stopAtDefault = true, $generateTree = false, $maxDepth = 0) {
        return $this->displayHelper->renderFolderView($folderView, $stopAtDefault, $generateTree, $maxDepth);
    }

    //Renders a report and its associated controls
    public function renderReportView($reportBuilder) {
        //If the passed in object is a string (a png from the report) then just return the string
        if (is_string($reportBuilder)) {
            $this->environment->display('MESDJasperReportBundle:Report:rawString.html.twig', array('output' => $reportBuilder));
        } else {
            return $this->displayHelper->renderReportView($reportBuilder);
        }
    }


    /**
     * Renders links for the html pages of a report
     *
     * @param  JasperClient\Client\Report $report The report object
     * @param  string                     $route  Symfony route for the action that handles html report page loads, optional, 
     *                                              will default to the route set in the config if not set
     *
     * @return string                             The rendered output
     */
    public function renderPageLinks($report, $route = null) {
        return $this->displayHelper->renderPageLinks($report, $route ?: $this->defaultPageRoute);
    }
}