<?php

namespace MESD\Jasper\ReportBundle\Client;

use JasperClient\Client\Client;

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

    //Boolean to track whether this class is connected to the service or not
    private $connected;

    //Saves the exception from a failed connection (useful for testing)
    private $connectionException;

    //Constructor (requires the secuirty context to get the user)
    public function __construct($securityContext) {
        //get the user (used to pass security onto the special input fields)
        $this->user = $securityContext->getToken()->getUser();

        //Set connected flag to false until the connect function is able to successfully login
        $this->connected = false;
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

    //Check if the object currently has a valid connection to the report server
    public function isConnected() {
        return $this->connected;
    }

    //Used for testing
    public function getServerInfo() {
        return $this->jasperClient->getServerInfo();
    }

}