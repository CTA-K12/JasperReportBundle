<?php

namespace Mesd\Jasper\ReportBundle\Twig\Extension;

use Mesd\Jasper\ReportBundle\Helper\DisplayHelper;

class ReportExtension extends \Twig_Extension
{
    ////////////////
    // VARIABLES  //
    ////////////////

    /**
     * The Jasper Reports Bundle display helper
     * @var DisplayHelper
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

    /**
     * The default route to handle assets
     * @var string
     */
    private $defaultAssetRoute;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     *
     * @param DisplayHelper $displayHelper The display helper reference
     */
    public function __construct(
        DisplayHelper $displayHelper,
                      $environment
    ) {
        //Set stuff
        $this->displayHelper = $displayHelper;
        $this->environment   = $environment;
    }

    //Get functions lists the functions in this class
    public function getFunctions()
    {
        //Function definition
        return
            [
            new \Twig_SimpleFunction(
                'mesd_report_render_page_links',
                [$this, 'renderPageLinks'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'mesd_report_render_output',
                [$this, 'renderReportOutput'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'mesd_report_render_export_links',
                [$this, 'renderExportLinks'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    //Returns the name of this extension (this is required)
    public function getName()
    {
        return 'mesd_report_extension';
    }

    ///////////////
    // FUNCTIONS //
    ///////////////

    /**
     * Renders links for the html pages of a report
     *
     * @param  JasperClient\Client\Report $report The report object
     * @param  string                     $route  Symfony route for the action that handles html report page loads, optional,
     *                                              will default to the route set in the config if not set
     *
     * @return string                             The rendered output
     */
    public function renderPageLinks(
        $report,
        $route = null
    ) {
        return $this->displayHelper->renderPageLinks($report, $route ?: $this->defaultPageRoute);
    }

    /**
     * Renders the output of a report
     *
     * @param  JasperClient\Client\Report $report The report to render
     *
     * @return string                             The rendered output
     */
    public function renderReportOutput($report)
    {
        return $this->displayHelper->renderReportOutput($report);
    }

    /**
     * Renders export links for a cached report
     *
     * @param  JasperClient\Client\Report $report      The report object to export
     * @param  string                     $exportRoute Optional override to the default export route
     *
     * @return string                                  Rendered output
     */
    public function renderExportLinks(
        $report,
        $exportRoute = null
    ) {
        return $this->displayHelper->renderExportLinks($report, $exportRoute);
    }
}
