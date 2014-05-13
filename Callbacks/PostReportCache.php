<?php

namespace MESD\Jasper\ReportBundle\Callbacks;

use JasperClient\Interfaces\PostReportCacheCallback;

use Doctrine\ORM\EntityManager;

class PostReportCache implements PostReportCacheCallback
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * Reference to the entity manager to use when dealing with database records
     * @var EntityManager
     */
    private $em;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param EntityManager $em The entity manager to use when dealing with database records
     */
    public function __construct(EntityManager $em) {
        //Set stuff
        $this->em = $em;
    }


    /////////////////////////
    // IMPLEMENTED METHODS //
    /////////////////////////


    /**
     * Function that is invoked if given to the client once a report has been cached
     *
     * @param string           $requestId        The request Id of the report being cached
     * @param array            $options          The options 
     * @param SimpleXMLElement $executionDetails The report execution request details XML
     */
    public function postReportCache($requestId, $options, $executionDetails) {
        //Find the report history entry with the given request id and update its status to cached
        $rh = $this->em->getRepository('MESDJasperReportBundle:ReportHistory')->findOneByRequestId($requestId);

        //Check that the report is not empty
        $totalPagess = $executionDetails->xpath('//reportExecution/totalPages');  
        //Yeah, I seriously called the variable that... I'm not good at naming things
        if (count($totalPagess) > 0) {
            $totalPages = (string)$totalPagess[0];
            if ($totalPages > 0) {
                $status = 'cached';
            } else {
                $status = 'empty';
            }
        } else {
            $status = 'empty';
        }

        if ($rh) {
            //Set the formats
            if (isset($options['formats'])) {
                $rh->setFormats(json_encode($options['formats']));
            }
            $rh->setStatus($status);
            $this->em->persist($rh);
            $this->em->flush($rh);
        }
    }
}