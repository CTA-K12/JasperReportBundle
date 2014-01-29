<?php

namespace MESD\Jasper\ReportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ReportFolderOpenEvent extends Event
{
    private $folderUri;

    public function __construct($folderUri = null) {
        $this->folderUri = $folderUri;
    }

    public function getFolderUri() {
        return $this->folderUri;
    }

    public function setFolderUri($folderUri) {
        $this->folderUri = $folderUri;
    }
}