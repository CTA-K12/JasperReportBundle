<?php

namespace MESD\Jasper\ReportBundle\Listener;

use MESD\Jasper\ReportBundle\Event\ReportFolderOpenEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class ReportFolderListener
{
    private $route;
    private $params;

    //Constructor
    public function __construct() {
        //empty for now
    }

    /*
     *  This class will listen for kernel request and extract the query parameters out of them
     *  Then, the folder open call will make use of them
     */
    public function onKernelRequest(GetResponseEvent $event) {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            //don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();

        //Set the route
        $this->route = $request->attributes->get('_route');

        //Get the parameters from the request
        $requestParams = array_merge($request->query->all(), $request->attributes->all());

        //Put all the params from the request that do not start with '_' into the class's param array
        $this->params = array();
        foreach($requestParams as $key => $rParam) {
            if (substr($key, 0, 1) != '_') {
                $this->params[$key] = $rParam;
            }
        }
    }

    public function onReportFolderOpen(ReportFolderOpenEvent $event) {
        //Check if the params contains an entry for folder
        if (!empty($this->params)) {
            if(array_key_exists('folderUri', $this->params)) {
                //If it does, set the folder uri of the event to that of the query param
                $folderUri = $this->params['folderUri'];
                //Replace the html code for '/' with '/' itself
                $folderUri = preg_replace('/%2/', '/', $folderUri);
                //Give the event the fixed string
                $event->setFolderUri($folderUri);
            }
        }
        //Stop this events propagation
        $event->stopPropagation();
    }
}