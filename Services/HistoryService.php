<?php

namespace MESD\Jasper\ReportBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;

class HistoryService
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * String name of the entity manager that is handling the report history records
     * @var string
     */
    private $entityManager;

    /**
     * Doctrine Registry Interface
     * @var Registry
     */
    private $doctrine;

    /**
     * Symfony Security Context
     * @var SecurityContext
     */
    private $securityContext;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param Registry        $doctrine        The doctrine registry interface
     * @param SecurityContext $securityContext The security context of the current active user
     */
    public function __construct(Registry $doctrine, SecurityContext $securityContext) {
        //Set stuff
        $this->doctrine = $doctrine;
        $this->securityContext = $securityContext;

        //Set the entity manager to default
        $entityManager = 'default';
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Gets history records for a particular report
     *
     * @param  string  $reportUri          The uri of the report to get records for
     * @param  boolean $limitByCurrentUser Whether to limit the return by only records associated with the current user
     * @param  options $options            Additional options for the report history repository filter call
     *
     * @return array                       The array of report history records
     */
    public function loadHistoryForReport($reportUri, $limitByCurrentUser = true, $options = []) {
        //Setup the options array
        $options['resource'] = $reportUri;

        //Restrict by the user if requested
        if ($limitByCurrentUser) {
            $options['username'] = $this->securityContext->getToken()->getUsername();
        }

        //Call the repository class and return the record entries
        return $this->getReportHistoryRepository()->filter($options);
    }


    /**
     * Return the entity manager assigned to handle report history records
     *
     * @return Doctrine\ORM\EntityManager The entity manager used by this service
     */
    public function getEM() {
        return $this->doctrine->getEntityManager($this->entityManager);
    }


    /**
     * Returns the report history repository using this services entity manager
     *
     * @return MESD\Jasper\ReportBundle\Repository\ReportHistoryRepository The report history repo
     */
    public function getReportHistoryRepository() {
        return $this->getEM()->getRepository('MESDJasperReportBundle:ReportHistory');
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the String name of the entity manager that is handling the report history records.
     *
     * @return string
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Sets the String name of the entity manager that is handling the report history records.
     *
     * @param string $entityManager the entity manager
     *
     * @return self
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }
}