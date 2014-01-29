<?php

namespace MESD\Jasper\ReportBundle\Client;

use JasperClient\Client\Client;
use JasperClient\Client\Report;
use JasperClient\Client\ReportBuilder;

use MESD\Jasper\ReportBundle\Event\ReportFolderOpenEvent;
use MESD\Jasper\ReportBundle\Event\ReportViewerRequestEvent;

class JasperReportClientServiceLayer 
{
    private $jasperClient;
    private $user;

    private $reportServer;
    private $reportUser;
    private $reportPass;
    private $reportDefaultFolder;
    private $reportUseCache;
    private $reportCacheDir;
    private $reportCacheTimeout;

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

    //Constructor (requires the secuirty context to get the user)
    public function __construct($securityContext, $eventDispatcher, $router) {
        //get the user (used to pass security onto the special input fields)
        $this->user = $securityContext->getToken()->getUser();

        //Set connected flag to false until the connect function is able to successfully login
        $this->connected = false;

        //Get the event dispatcher
        $this->eventDispatcher = $eventDispatcher;

        //Get the router
        $this->router = $router;
    }

    /*
     * The following a set methods used by the extension class to set stuff from the config file
     */

    public function setReportUsername($username) {
        $this->reportUser = $username;
    }

    public function setReportPassword($password) {
        $this->reportPass = $password;
    }

    public function setReportServer($server) {
        $this->reportServer = $server;
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

    //Connect to the server (this is mostly used by the report bundle extension class)
    public function connect() {
        try {
            $this->jasperClient = new Client($this->reportServer, $this->reportUser, $this->reportPass);
            $this->jasperClient->login();
        } catch (\Exception $e) {
            //Set the connection exception to e and return false
            $this->connectionException = $e;
            $this->connected = false;
            return false;
        }

        //If the catch block was not invoked set the connected flag to true and return true
        $this->connected = true;
        return true;
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
        $assetClient = new Client($this->reportServer, $this->reportUser, $this->reportPass, $jSessionId);
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

    //Check if the object currently has a valid connection to the report server
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

}