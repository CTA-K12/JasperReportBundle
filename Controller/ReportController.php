<?php

namespace MESD\Jasper\ReportBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends ContainerAware
{
    /**
     * Display a list of reports and folders in a given folder object
     * 
     * This action will look for the query parameter folderUri to take in a 
     * path to a folder.  If not present, will use the default folder from config
     *
     * @return Reponse
     */
    public function resourceListAction()
    {
        //Get the folderUri query parameter if it exists
        $folderUri = $this->container->get('request')->query->get('folderUri');

        //Get the folder contents from the client service (if folderUri is null, this returns the resources of the default folder)
        try {
            $resourceCollection = $this->container->get('mesd.jasperreport.client')->getFolder($folderUri);
        } catch (\Exception $e) {
            return new Response('Folder requested was not valid');
        }

        //If there were no resources returned (empty folder) just set the collection to an empty array
        if (!$resourceCollection) {
            $resourceCollection = array();
        }

        //Get the rendering parameters from the config via the service
        $renderingParams = $this->container->get('mesd.jasperreport.client')->getRenderingParameters();

        //Render the list of contents in the twig template
        return $this->container->get('templating')->renderResponse('MESDJasperReportBundle:Report:resourceList.html.twig', array(
            'resources'         => $resourceCollection,
            'renderingParams'   => $renderingParams
        ));
    }

    /**
     * Renders a given report and its associated input fields
     * 
     * requires the reportUri to be in the route
     *
     * @return Reponse
     */
    public function reportViewAction($reportUri) 
    {
        //Try to get the requested report
        try {

        } catch (\Exception $e) {
            return new Response('Report request was not valid')
        }
    }
}