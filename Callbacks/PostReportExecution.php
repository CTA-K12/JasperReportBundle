<?php

namespace MESD\Jasper\ReportBundle\Callbacks;

use JasperClient\Interfaces\PostReportExecutionCallback;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use MESD\Jasper\ReportBundle\Entity\ReportHistory;
use JasperClient\Client\JasperHelper;

class PostReportExecution implements PostReportExecutionCallback
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * Reference to the entity manager set in the configuration
     * @var EntityManager
     */
    private $em;

    /**
     * Reference to Symfony's security context
     * @var SecurityContext
     */
    private $securityContext;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param EntityManager   $em              The entity manager to use when placing report execution records into the database
     * @param SecurityContext $securityContext The security context to use when saving information into the database
     */
    public function __construct(EntityManager $em, SecurityContext $securityContext) {
        //Set stuff
        $this->em = $em;
        $this->securityContext = $securityContext;
    }


    /////////////////////////
    // IMPLEMENTED METHODS //
    /////////////////////////


    /**
     * The function to be invoked once the report has been executed
     *
     * @param string           $requestId        The request Id of the report being cached
     * @param array            $options          The options 
     * @param SimpleXMLElement $executionDetails The report execution request details XML
     */
    public function postReportExecution($resource, $options, $response) {
        //Create a new instance of the report history entity
        $rh = new ReportHistory();

        //Convert the parameters to json
        if (isset($options['parameters'])) {
            $rh->setParameters(json_encode($options['parameters']));
        } else {
            $rh->setParameters('{}');
        }

        //Set formats to empty until post cache
        $rh->setFormats('{}');

        //Get the request id from the response
        $rh->setRequestId(JasperHelper::getRequestIdFromDetails($response));

        //Set everything else
        $rh->setDate(new \DateTime());
        $rh->setReportUri($resource);
        $rh->setUsername($this->securityContext->getToken()->getUsername());
        $rh->setStatus('executed');

        //Persist and flush
        $this->em->persist($rh);
        $this->em->flush($rh);
    }
}