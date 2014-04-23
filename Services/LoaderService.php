<?php

namespace MESD\Jasper\ReportBundle\Services;

use JasperClient\Client\ReportLoader;

/**
 * Serves as a wrapper for the client libraries report loader
 */
class LoaderService
{
    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * Reference to Symfony's routing service
     * @var Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * Whether to attach an asset url 
     * @var boolean
     */
    private $defaultAttachAssetUrl;

    /**
     * Default asset route to attach to report outputs that are loaded
     * @var string
     */
    private $defaultAssetRoute;

    /**
     * Optional parameters to give to the router when generating the url for the asset route
     * @var array
     */
    private $routeParameters;

    /**
     * The directory where reports are stored
     * @var string
     */
    private $reportCacheDir;

    /**
     * Page to default to
     * @var int
     */
    private $defaultPage;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param SymfonyComponentRoutingRouter $router [description]
     */
    public function __construct(\Symfony\Component\Routing\Router $router) {
        //Set stuff
        $this->router = $router;

        //Init
        $routeParameters = array();
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Builds a report loader with the given parameters
     *
     * @param  boolean $attachAssetUrl Optional override for the default attach asset url boolean flag
     * @param  string  $assetRoute     Optional override for the default asset route
     * @param  array   $routeParamters Optional override for the set route parameters array
     *
     * @return ReportLoader            The report loader
     */
    public function getReportLoader($attachAssetUrl = null, $assetRoute = null, $routeParameters = null) {
        //Determine whether to use the defaults or not
        $attachAssetUrl = $attachAssetUrl ?: $this->defaultAttachAssetUrl;
        $assetRoute = $assetRoute ?: $this->defaultAssetRoute;
        $routeParameters = $routeParameters ?: $this->routeParameters;

        //Add the asset and request id placeholders into the route parameters array
        $routeParameters['asset'] = '!asset!';
        $routeParameters['requestId'] = '!requestId!';

        //Create the asset url
        if ($attachAssetUrl) {
            $assetUrl = $this->router->generate($assetRoute, $routeParameters);
        }

        //Create a new report loader instance
        return new ReportLoader($this->reportCacheDir, $attachAssetUrl, $assetUrl, $this->defaultPage);
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the Reference to Symfony's routing service.
     *
     * @return Symfony\Component\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Sets the Reference to Symfony's routing service.
     *
     * @param Symfony\Component\Routing\Router $router the router
     *
     * @return self
     */
    public function setRouter(Symfony\Component\Routing\Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Gets the Whether to attach an asset url.
     *
     * @return boolean
     */
    public function getDefaultAttachAssetUrl()
    {
        return $this->defaultAttachAssetUrl;
    }

    /**
     * Sets the Whether to attach an asset url.
     *
     * @param boolean $defaultAttachAssetUrl the default attach asset url
     *
     * @return self
     */
    public function setDefaultAttachAssetUrl($defaultAttachAssetUrl)
    {
        $this->defaultAttachAssetUrl = $defaultAttachAssetUrl;

        return $this;
    }

    /**
     * Gets the Default asset route to attach to report outputs that are loaded.
     *
     * @return string
     */
    public function getDefaultAssetRoute()
    {
        return $this->defaultAssetRoute;
    }

    /**
     * Sets the Default asset route to attach to report outputs that are loaded.
     *
     * @param string $defaultAssetRoute the default asset route
     *
     * @return self
     */
    public function setDefaultAssetRoute($defaultAssetRoute)
    {
        $this->defaultAssetRoute = $defaultAssetRoute;

        return $this;
    }

    /**
     * Gets the Optional parameters to give to the router when generating the url for the asset route.
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Sets the Optional parameters to give to the router when generating the url for the asset route.
     *
     * @param array $routeParameters the route parameters
     *
     * @return self
     */
    public function setRouteParameters($routeParameters = [])
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Gets the The directory where reports are stored.
     *
     * @return string
     */
    public function getReportCacheDir()
    {
        return $this->reportCacheDir;
    }

    /**
     * Sets the The directory where reports are stored.
     *
     * @param string $reportCacheDir the report cache
     *
     * @return self
     */
    public function setReportCacheDir($reportCacheDir)
    {
        $this->reportCacheDir = $reportCacheDir;

        return $this;
    }

    /**
     * Gets the Page to default to.
     *
     * @return int
     */
    public function getDefaultPage()
    {
        return $this->defaultPage;
    }

    /**
     * Sets the Page to default to.
     *
     * @param int $defaultPage the default page
     *
     * @return self
     */
    public function setDefaultPage($defaultPage)
    {
        $this->defaultPage = $defaultPage;

        return $this;
    }
}