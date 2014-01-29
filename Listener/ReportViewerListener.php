<?php

namespace MESD\Jasper\ReportBundle\Listener;

use MESD\Jasper\ReportBundle\Event\ReportViewerRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class ReportViewerListener
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

    public function onReportViewerRequest(ReportViewerRequestEvent $event) {
        //Check if any report params are in the request and update the event object if they are
        if (!empty($this->params)) {
            if(array_key_exists('reportPage', $this->params)) {
                //Set the page number
                $event->setReportPage($this->params['reportPage']);
            }
            if (array_key_exists('reportFormat', $this->params)) {
                //Set the format
                $event->setReportFormat($this->params['reportFormat']);
            }
            if (array_key_exists('uri', $this->params)) {
                //Set the format
                $event->setAssetUri($this->params['uri']);
            }
            if (array_key_exists('jsessionid', $this->params)) {
                //Set the format
                $event->setJSessionId($this->params['jsessionid']);
            }
        }
        //Stop this events propagation
        $event->stopPropagation();
    }
}