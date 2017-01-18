<?php

namespace Mesd\Jasper\ReportBundle\Callbacks;

use Doctrine\ORM\EntityManager;
use JasperClient\Client\JasperHelper;
use JasperClient\Interfaces\PostReportExecutionCallback;
use Mesd\Jasper\ReportBundle\Entity\ReportHistory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

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
     * @var TokenStorage
     */
    private $tokenStorage;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     *
     * @param EntityManager   $em              The entity manager to use when placing report execution records into the database
     * @param TokenStorage $tokenStorage The security context to use when saving information into the database
     */
    public function __construct(
        EntityManager $em,
        TokenStorage  $tokenStorage
    ) {
        //Set stuff
        $this->em           = $em;
        $this->tokenStorage = $tokenStorage;
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
    public function postReportExecution(
        $resource,
        $options,
        $response
    ) {
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
        $rh->setUsername($this->tokenStorage->getToken()->getUsername());
        $rh->setStatus('executed');

        //Persist and flush
        $this->em->persist($rh);
        $this->em->flush($rh);
    }
}
