<?php

namespace MESD\Jasper\ReportBundle\Client;

use JasperClient\Client\Client;

class JasperReportClientServiceLayer 
{
    private $jasperClient;
    private $user;

    private $appReportServer;
    private $appReportUser;
    private $appReportPass;
    private $appReportDefaultFolder;
    private $appReportUseCache;
    private $appReportCacheDir;
    private $appReportCacheTimeout;

    private $ft;

    public function __construct() {
        //Set the parameters
        // $this->appReportServer = $appReportServer
        // $this->appReportUser = $appReportUser
        // $this->appReportPass = $appReportPass
        // $this->appReportDefaultFolder = $appReportDefaultFolder
        // $this->appReportUseCache = $appReportUseCache
        // $this->appReportCacheDir = $appReportCacheDir
        // $this->appReportCacheTimeout = $appReportCacheTimeout
        // $this->securityContext = $securityContext

        /*//Build the japser client object
        $this->jasperClient = new Client(null, $this->appReportHost, $this->appReportUser, $this->appReportPass);

        //get the user 
        $this->user = $securityContext->getToken()->getUser();*/
    }

    public function getServerInfo() {
        return $this->jasperClient->getServerInfo();
    }

    public function setFunTimeString($string) {
        $this->ft = $string;
    }

    public function getFunTimeString($string) {
        return $this->ft;
    }


}