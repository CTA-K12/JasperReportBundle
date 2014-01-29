<?php

namespace MESD\Jasper\ReportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ReportViewerRequestEvent extends Event
{
    private $reportPage;  //The page number of the report (only matters if format is html)
    private $reportFormat;  //The format of the report (html, pdf, xls)

    private $assetUri;  
    private $jSessionId;

    public function __construct($reportFormat = 'html', $reportPage = 1) {
        $this->reportPage = $reportPage;
        $this->reportFormat = $reportFormat;

        $this->assetUri = null;
        $this->jSessionId = null;
    }

    public function getReportPage() {
        return $this->reportPage;
    }

    public function setReportPage($reportPage) {
        $this->reportPage = $reportPage;
    }

    public function getReportFormat() {
        return $this->reportFormat;
    }

    public function setReportFormat($reportFormat) {
        $this->reportFormat = $reportFormat;
    }

    public function getAssetUri() {
        return $this->assetUri;
    }

    public function setAssetUri($assetUri) {
        $this->assetUri = $assetUri;
    }

    public function getJSessionId() {
        return $this->jSessionId;
    }

    public function setJSessionId($jSessionId) {
        $this->jSessionId = $jSessionId;
    }

    public function isAsset() {
        return ($this->assetUri != null && $this->jSessionId != null) ? true : false;
    }

}