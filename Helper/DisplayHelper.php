<?php

namespace Mesd\Jasper\ReportBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

use Mesd\Jasper\ReportBundle\Helper\PageLinkManager;

class DisplayHelper 
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const FORMAT_HTML = 'html';

    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * The Symfony service container
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * The default route to use for handling cached report page loads
     * @var string
     */
    private $defaultDisplayPageRoute;

    /**
     * The default route to handle cached report exports
     * @var string
     */
    private $defaultExportRoute;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *   NOTE: This helper class requires the twig engine so it can render twigs to pass back to the twig extension
     *         BUT giving this class the templating engine service directly creates a circular reference, which can
     *         be worked around by just using the container
     *
     * @param ContainerInterface $container [description]
     */
    public function __construct(ContainerInterface $container) {
        //Set stuff
        $this->container = $container; 
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Renders a set of page links for html report views
     *
     * @param  JasperClient\Client\Report $report Report Object
     * @param  string                     $route  The route to the action that handles html page loads
     *
     * @return string                             The rendered output of the report page links
     */
    public function renderPageLinks(\JasperClient\Client\Report $report, $route = null) {
        //Set the route to the default if its not set
        $route = $route ?: $this->defaultDisplayPageRoute;

        //Check that the report is in html and has a page and total page number
        if ('html' !== $report->getFormat() || null === $report->getPage() || null === $report->getTotalPages() || null == $route) {
            return '';
        }

        //Generate the urls
        $url = $this->container->get('templating.helper.router')->generate($route, array('page' => '!page!', 'requestId' => $report->getRequestId()));
        $pageLinks = new PageLinkManager();
        $pageLinks->generatePageLinks($url, $report->getPage(), $report->getTotalPages());

        //Render
        return $pageLinks->printLinks();
    }


    /**
     * Renders the output of a report object
     *
     * @param  JasperClient\Client\Report $report The report object to render
     *
     * @return string                             Rendered report view
     */
    public function renderReportOutput(\JasperClient\Client\Report $report) {
        //Check that the report is in html format
        if ('html' !== $report->getFormat()) {
            return '';
        }

        //Render the report viewer twig
        return $this->container->get('templating')->render(
            'MesdJasperReportBundle:Report:report.html.twig', array(
                    'report' => $report
                )
            );
    }


    /**
     * Renders export links for a report
     *
     * @param  JasperClient\Client\Report $report      The cached report to generate export links for
     * @param  string                     $exportRoute Optional override to the default export route
     *
     * @return string                                  The rendered output
     */
    public function renderExportLinks(\JasperClient\Client\Report $report, $exportRoute = null) {
        //Determine whether to use the default export route
        $exportRoute = $exportRoute ?: $this->defaultExportRoute;

        //Generate the links for each export type the report is available in
        $exportLinks = array();
        foreach($report->getAvailableFormats() as $format) {
            //Ignore html
            if (self::FORMAT_HTML !== $format) {
                $exportLinks[$format] = $this->container->get('router')
                    ->generate($exportRoute, array('format' => $format, 'requestId' => $report->getRequestId()));
            }
        }

        //Render the partial twig
        return $this->container->get('templating')->render(
            'MesdJasperReportBundle:Report:exportLinks.html.twig', array(
                    'exportLinks' => $exportLinks
                )
            );
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the The Symfony service container.
     *
     * @return Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the The Symfony service container.
     *
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container the container
     *
     * @return self
     */
    public function setContainer(Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Gets the The default route to use for handling cached report page loads.
     *
     * @return string
     */
    public function getDefaultDisplayPageRoute()
    {
        return $this->defaultDisplayPageRoute;
    }

    /**
     * Sets the The default route to use for handling cached report page loads.
     *
     * @param string $defaultDisplayPageRoute the default display page route
     *
     * @return self
     */
    public function setDefaultDisplayPageRoute($defaultDisplayPageRoute)
    {
        $this->defaultDisplayPageRoute = $defaultDisplayPageRoute;

        return $this;
    }

    /**
     * Gets the The default route to handle cached report exports.
     *
     * @return string
     */
    public function getDefaultExportRoute()
    {
        return $this->defaultExportRoute;
    }

    /**
     * Sets the The default route to handle cached report exports.
     *
     * @param string $defaultExportRoute the default export route
     *
     * @return self
     */
    public function setDefaultExportRoute($defaultExportRoute)
    {
        $this->defaultExportRoute = $defaultExportRoute;

        return $this;
    }
}