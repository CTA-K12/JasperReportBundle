<?php

namespace MESD\Jasper\ReportBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contains actions to help with display and interface.
 * To use these actions the report bundle's routes will need to be included into the main projects route file
 *
 * NOTE: these actions are to remain OPTIONAL, and should not be required to use the report bundle
 */
class ReportController extends ContainerAware
{
    /**
     * Returns the resource with the given uri
     * 
     * Gets parameters via query string in the request
     *   uri -> uri of the asset to get
     *   jsessionid -> the current jsessionid of the report to get the asset for
     * 
     * @return string             The raw output of the asset in string form
     */
    public function downloadReportAssetAction() {
        //Get the query string parameter
        $assetUri = $this->container->get('request')->query->get('uri');
        $jSessionId = $this->container->get('request')->query->get('jsessionid');

        //Get the asset from the client
        $asset = $this->container->get('mesd.jasperreport.client')->getReportAsset($assetUri, $jSessionId);

        //Return the response
        return new Response($asset);
    }
}