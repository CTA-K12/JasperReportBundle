<?php

namespace MESD\Jasper\ReportBundle\Client;

use JasperClient\Client\Client;
use JasperClient\Client\Report;
use JasperClient\Client\ReportBuilder;
use JasperClient\Client\ReportLoader;

use MESD\Jasper\ReportBundle\Event\ReportFolderOpenEvent;
use MESD\Jasper\ReportBundle\Event\ReportViewerRequestEvent;

use MESD\Jasper\ReportBundle\Exception\JasperNotConnectedException;
use MESD\Jasper\ReportBundle\Factories\InputControlFactory;

use Symfony\Component\DependencyInjection\Container;

/**
 * Service class that acts as a wrapper around the jasper client class in the jasper client library
 */
class JasperReportClientServiceLayer 
{
    ///////////////
    // CONSTANTS //
    ///////////////
    
    const DEFAULT_REPORT_FORMAT = 'html';
    const DEFAULT_REPORT_PAGE_NUMBER = 1;
    
    const FALLBACK_ASSET_URL = '';

    //These are the placeholders that are given to the routers generate function, which CANNOT have the '{}' characters Jasper looks for
    const ASSET_ROUTE_CONTEXT_PATH_PLACEHOLDER = 'tempvar-contextPath';
    const ASSET_ROUTE_REPORT_EXECUTION_ID_PLACEHOLDER = 'tempvar-reportExecutionId';
    const ASSET_ROUTE_EXPORT_OPTIONS_PLACEHOLDER = 'tempvar-exportOptions';

    //These are the placeholders that Jasper will look for that will replace the ones placed into the url originally
    const ASSET_ROUTE_CONTEXT_PATH_JASPER_VAR = '{contextPath}';
    const ASSET_ROUTE_REPORT_EXECUTION_ID_JASPER_VAR = '{reportExecutionId}';
    const ASSET_ROUTE_EXPORT_OPTIONS_JASPER_VAR = '{exportOptions}';

    //Error Messages
    const EXCEPTION_OPTIONS_HANDLER_NOT_INTERFACE = 'Requested Options Handler service does not implement Options Handler Interface';

    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * Reference to the jasper client that is initialized by the connect method
     * with the parameters passed via dependency injection
     * @var JasperClient\Client\Client
     */
    private $jasperClient;

    /**
     * Default symfony route to send asset requests to 
     * @var string
     */
    private $defaultAssetRoute;

    /**
     * The Symfony Service Container
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

    private $reportHost;
    private $reportUser;
    private $reportPass;
    private $reportDefaultFolder;
    private $reportUseCache;
    private $reportCacheDir;
    private $reportCacheTimeout;
    
    private $optionHandlerServiceName;

    private $eventDispatcher;
    private $router;
    private $routeHelper;

    private $openFolderIcon;
    private $closeFolderIcon;
    private $reportIcon;

    //Boolean to track whether this class is connected to the service or not
    private $connected;

    //Saves the exception from a failed connection (useful for testing)
    private $connectionException;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor used via Symfony's dependency injection container to intialize the needed dependencies
     * 
     * @param Symfony\Component\DependencyInjection\Container $container The Symfony Service Container
     */
    public function __construct(Container $container) {
        //Set stuff
        $this->container = $container;

        //Set connected flag to false until the connect function is able to successfully login
        $this->connected = false;

        
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Connect to the Jasper Report Server with the current set of parameters
     * (This is function is called automatically during the dependency injection container setup)
     * 
     * @return boolean Indicator of whether the connection was successful
     */
    public function connect() {
        //Attempt to initialize the client and login to the report server
        try {
            //Give this object's stored parameters to initialize the jasper client 
            $this->jasperClient = new Client($this->reportHost, $this->reportUser, $this->reportPass);

            //Login and set the connection flag to the return of the login method
            $this->connected = $this->jasperClient->login();
        } catch (\Exception $e) {
            //Set the connection status to false
            $this->connected = false;

            //Rethrow the exception
            throw $e;
        }

        //Return the connection flag
        return $this->connected;
    }



    public function buildReportInputForm($reportUri, $getICFrom = 'Fallback', $data = null, $options = []) {
        //Get the options handler from the dependency container
        $optionsHandler = $this->container->get($this->optionHandlerServiceName);

        //Check that the options handler implements the option handler interface
        if (!in_array('MESD\Jasper\ReportBundle\Interfaces\OptionsHandlerInterface', class_implements($optionsHandler))) {
            throw new \Exception(self::EXCEPTION_OPTIONS_HANDLER_NOT_INTERFACE);
        }

        //Create a new input control factory
        $icFactory = new InputControlFactory($optionsHandler, $getICFrom, 'MESD\Jasper\ReportBundle\InputControl\\');

        //Load the input controls from the client using the factory and the options handler
        $inputControls = $this->jasperClient->getReportInputControl($reportUri, $getICFrom, $icFactory);

        //Build the form
        $form = $this->container->get('form.factory')->createBuilder('form', $data, $options);
        foreach($inputControls as $inputControl) {
            $inputControl->attachInputToFormBuilder($form);
        }

        //Return the completed form
        return $form->getForm();
    }



    ////////
    // GETTERS AND SETTERS
    //////

    /**
     * Sets the default asset route
     * 
     * @param  string $defaultAssetRoute The string representation of a symfony route to set as the default asset route
     *
     * @return MESD\Jasper\ReportBundle\Client\JasperReportClientServiceLayer Reference to this
     */
    public function setDefaultAssetRoute($defaultAssetRoute) {
        $this->defaultAssetRoute = $defaultAssetRoute;

        return $this;
    }

    /**
     * Gets the default asset route
     *
     * @return string The symfony route that was set as the default to handle asset requests
     */
    public function getDefaultAssetRoute() {
        return $this->defaultAssetRoute;
    }

    public function setReportUsername($username) {
        $this->reportUser = $username;
    }

    public function setReportPassword($password) {
        $this->reportPass = $password;
    }

    public function setReportHost($host) {
        $this->reportHost = $host;
    }

    public function setReportDefaultFolder($defaultFolder) {
        $this->reportDefaultFolder = $defaultFolder;
    }

    public function setReportUseCache($useCache) {
        $this->reportUseCache = $useCache;
    }

    public function setReportCacheDir($cacheDir) {
        $this->reportCacheDir = $cacheDir;
    }

    public function setReportCacheTimeout($cacheTimeout) {
        $this->reportCacheTimeout = $cacheTimeout;
    }

    //Gets for the non confidential settings
    public function getReportDefaultFolder() {
        return $this->reportDefaultFolder;
    }

    public function getReportUseCache() {
        return $this->reportUseCache;
    }

    public function getReportCacheDir() {
        return $this->reportCacheDir;
    }

    public function getReportCacheTimeout() {
        return $this->reportCacheTimeout;
    }

    //Get a folder resource (leavng the argument null returns the default folder)
    public function getFolder($folderUri = null) {
        //If the connection is valid, then try and get the resourceCollection with the given or default folderUri
        if ($this->isConnected()) {
            if ($folderUri) {
                return $this->jasperClient->getFolder($folderUri, $this->reportUseCache, $this->reportCacheDir, $this->reportCacheTimeout);
            } else {
                return $this->jasperClient->getFolder($this->reportDefaultFolder, $this->reportUseCache, $this->reportCacheDir, $this->reportCacheTimeout);
            }
        } else {
            return false;
        }
    }

    //Get a folder view, works like getFolder, but will also look for query parameters in the url and open the subfolder as necessary
    //when used with the twig function, it will automatically handle opening folders
    //Note, getFolderView returns the folder collection wrapped in an array keyed by parent folder uri (to generate links back to the parent)
    public function getFolderView($folderUri = null) {
        if ($this->isConnected()) {
            //If the folder is not specified, set to default
            if (!$folderUri) {
                $folderUri = $this->reportDefaultFolder;
            }

            //Send out the event to the listener
            $folderEvent = new ReportFolderOpenEvent($folderUri);
            $this->eventDispatcher->dispatch('mesd.jasperreport.report_folder_open', $folderEvent);
            if ($folderEvent->isPropagationStopped()) {
                $folderUri = $folderEvent->getFolderUri();
            }

            //Open the folder uri in the jasper client and return it
            $contents = $this->jasperClient->getFolder($folderUri, $this->reportUseCache, $this->reportCacheDir, $this->reportCacheTimeout);

            //Get the parent uri
            $strippedUri = rtrim($folderUri, '/');
            $uriChunks = explode('/', $strippedUri);
            $parentUri = '';
            for($i = 0; $i < count($uriChunks) - 1; $i++) {
                if (!empty($uriChunks[$i])) {
                    $parentUri = $parentUri . '/' . $uriChunks[$i];
                }
            }

            return array($parentUri => $contents);
        }
    }

    //Get a report
    public function getReport($reportUri, $format = 'html') {
        if ($this->isConnected()) {
            $report = null;
            if ($report) {
                return $report;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *  buildReport
     *
     *  takes in a reportUri and creates a ReportBuilder object and builds it
     *
     *  @return ReportBuilder
     */
    public function buildReport($reportUri, $format = 'html', $assetUrl = '') 
    {
        //Check connection
        if ($this->isConnected()) {
            //Get the report
            $report = new Report($reportUri, $format);

            //Create an instance of the report builder
            $reportBuilder = new ReportBuilder(
                  $this->jasperClient
                , $report
                , '&page=1'
                , $assetUrl
                , 'Fallback'
                );
        } else {
            return false;
        }
    }

    //Get a report view, like get report, but will handle query parameters and render the correct page
    //can be passed to the mesd_jasperreport_report_view twig function to automatically render the controls and report
    public function getReportView($reportUri, $format = 'html') {
        if ($this->isConnected()) {
            //Create an event with default params and pass it to the event listener to process
            $reportViewerEvent = new ReportViewerRequestEvent($format, 1);
            $this->eventDispatcher->dispatch('mesd.jasperreport.report_viewer_request', $reportViewerEvent);
            if ($reportViewerEvent->isPropagationStopped()) {
                if ($reportViewerEvent->isAsset()) {
                    return $this->getReportAsset($reportViewerEvent->getAssetUri(), $reportViewerEvent->getJSessionId());
                } else {
                    $params = '&page=' . $reportViewerEvent->getReportPage();
                    $format = $reportViewerEvent->getReportFormat();
                }
            } else {
                $params = '&page=1';
            }

            //Create new report object
            $report = new Report($reportUri, $format);

            //Create the builder
            $reportBuilder = new ReportBuilder(
                  $this->jasperClient
                , $report
                , $params
                , $this->router->getMatcher()->getContext()->getBaseUrl() . $this->router->getMatcher()->getContext()->getPathInfo() . '?asset=true'
                , 'Fallback'
                );

            return $reportBuilder;
        } else {
            return false;
        }
    }

    //Returns specified asset from a report with the specified jsessionid
    public function getReportAsset($assetUri, $jSessionId) {
        //Create a connection with the given jSessionId
        $assetClient = new Client($this->reportHost, $this->reportUser, $this->reportPass, $jSessionId);
        return $assetClient->getReportAsset($assetUri);
    }

    //Get input control
    public function getReportInputControl($resource, $getICFrom) {
        if ($this->isConnected()) {
            $inputControl = $this->jasperClient->getReportInputControl($resource, $getICFrom);
            if ($inputControl) {
                return $inputControl;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //Get ReportBuilder
    public function getReportBuilder($report) {

    }

    //Check if the object currently has a valid connection to the report host
    public function isConnected() {
        return $this->connected;
    }

    //Used for testing
    public function getServerInfo() {
        return $this->jasperClient->getServerInfo();
    }

    /**
     *  Set the openFolderIcon class for the report controller
     */
    public function setOpenFolderIcon($openFolderIcon) {
        $this->openFolderIcon = $openFolderIcon;
    }

    /**
     *  Get the openFolderIcon class 
     *
     *  @return string
     */
    public function getOpenFolderIcon() {
        return $this->openFolderIcon;
    }

    /**
     *  Set the closedFolderIcon class for the report controller
     */
    public function setClosedFolderIcon($closedFolderIcon) {
        $this->closedFolderIcon = $closedFolderIcon;
    }

    /**
     *  Get the closedFolderIcon class 
     *
     *  @return string
     */
    public function getClosedFolderIcon() {
        return $this->closedFolderIcon;
    }

    /**
     *  Set the reportIcon class for the report controller
     */
    public function setReportIcon($reportIcon) {
        $this->reportIcon = $reportIcon;
    }

    /**
     *  Get the reportIcon class 
     *
     *  @return string
     */
    public function getReportIcon() {
        return $this->reportIcon;
    }

    /**
     *  Returns the rendering parameters either specified in the users config or the bundle's defaults
     *
     *  @return array
     */
    public function getRenderingParameters() {
        return array(
              'openFolderIcon'      => $this->openFolderIcon
            , 'closedFolderIcon'    => $this->closedFolderIcon
            , 'reportIcon'          => $this->reportIcon
        );
    }

    //TEST!!!!!!!
    public function startReportExecution($a, $b = []) {
        return $this->jasperClient->startReportExecution($a, $b);
    }

    public function getExecutedReport($a, $f = 'html') {
        return $this->jasperClient->getExecutedReport($a, $f);
    }

    public function pollReportExecution($a) {
        return $this->jasperClient->pollReportExecution($a);
    }

    public function getReportExecutionStatus($a) {
        return $this->jasperClient->getReportExecutionStatus($a);
    }

    public function cacheReportExecution($a, $b = []) {
        return $this->jasperClient->cacheReportExecution($a, $b);
    }

    public function getReportOutput($a, $b, $c = [], $d) {
        $r = new ReportLoader($d);
        return $r->getCachedReport($a, $b, $c);
    }

    public function createReportBuilder($a, $b = 'Jasper') {
        return $this->jasperClient->createReportBuilder($a, $b);
    }


    ////////////////////////
    // GETTERS AND SETTER //
    ////////////////////////

    /**
     * Sets the option handler service name
     * @param string $optionHandlerServiceName The option handler service name
     */
    public function setOptionHandlerServiceName($optionHandlerServiceName) {
        $this->optionHandlerServiceName = $optionHandlerServiceName;

        return $this;
    }


    /**
     * Returns the option handler service name
     * @return string Option handler service name
     */
    public function getOptionHandlerServiceName() {
        return $this->optionHandlerServiceName;
    }
}