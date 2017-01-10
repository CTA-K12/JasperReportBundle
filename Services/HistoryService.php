<?php

namespace Mesd\Jasper\ReportBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Mesd\Jasper\ReportBundle\Services\ClientService;
use Symfony\Component\Security\Core\TokenStorage;

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
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * The Client Service
     * @var ClientService
     */
    private $clientService;

    /**
     * An array of report input controls that used when displaying history records to speed up the process
     * @var array
     */
    private $inputControlStash;

    //////////////////
    // BASE METHODS //
    //////////////////

    /**
     * Constructor
     *
     * @param Registry        $doctrine        The doctrine registry interface
     * @param TokenStorage    $tokenStorage    The security context of the current active user
     * @param ClientService   $clientService   The client service
     */
    public function __construct(
        Registry      $doctrine,
        TokenStorage  $tokenStorage,
        ClientService $clientService
    ) {
        //Set stuff
        $this->doctrine      = $doctrine;
        $this->tokenStorage  = $tokenStorage;
        $this->clientService = $clientService;

        //Set the entity manager to default
        $this->entityManager = 'default';

        //Prep the input control stash
        $this->inputControlStash = [];
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
    public function loadHistoryForReport(
        $reportUri,
        $limitByCurrentUser = true,
        $options = []
    ) {
        //Setup the options array
        $options['resource'] = $reportUri;

        //Restrict by the user if requested
        if ($limitByCurrentUser) {
            $options['username'] = $this->tokenStorage->getToken()->getUsername();
        }

        //Call the repository class and return the record entries
        return $this->getReportHistoryRepository()->filter($options);
    }

    /**
     * Gets history records for a particular report or all reports and places them in more display friendly fashion
     *
     * @param  string  $reportUri          The uri of the report to get records for, if null, all reports will be grabbed
     * @param  boolean $limitByCurrentUser Whether to limit the return by only records associated with the current user
     * @param  options $options            Additional options for the report history repository filter call
     *
     * @return array                       The array of report history records
     */
    public function getReportHistoryDisplay(
        $reportUri = null,
        $limitByCurrentUser = true,
        $options = []
    ) {
        //Call the load method with the given parameters
        if ($reportUri) {
            $history = $this->loadHistoryForReport($reportUri, $limitByCurrentUser, $options);
        } else {
            $history = $this->loadRecentHistory($limitByCurrentUser, $options);
        }

        //Create an array to hold the pieces
        $displayArray = [];

        //Foreach history record, prettify the selection and check that it is showable to the current user
        foreach ($history as $record) {
            //If the return is false, skip the rest for this record as this user cannot view it at this time
            $recordArray = [];

            // //Get the input controls for the report
            // $inputControls = $this->clientService->getReportInputControls($record->getReportUri(), $this->clientService->getDefaultInputOptionsSource());

            // //Foreach input control match it with the prettified selections
            // $displayParams = array();
            // foreach($inputControls as $inputControl) {
            //     var_dump($params); die;
            //     if (isset($params[$inputControl->getId()])) {
            //         $displayParams[$inputControl->getLabel()] = $params[$inputControl->getId()];
            //     } else {
            //         $displayParams[$inputControl->getLabel()] = array(' ~ ');
            //     }
            // }

            //Convert the columns parameter into something easier to read
            $params = $this->prettifyParameters($record->getReportUri(), json_decode($record->getParameters(), true));

            //Fill out the rest of the
            $recordArray['report']     = $record->getReportUri();
            $recordArray['requestId']  = $record->getRequestId();
            $recordArray['date']       = $record->getDate();
            $recordArray['username']   = $record->getUsername();
            $recordArray['status']     = $record->getStatus();
            $recordArray['formats']    = $record->getFormats();
            $recordArray['parameters'] = $params;

            //Add the record array to the display array
            $displayArray[] = $recordArray;
        }

        //Return the display friendly array
        return $displayArray;
    }

    /**
     * Converts the JSON parameters column into something human readable
     *
     * @param  string $reportUri  The uri of the report the parameters are associated with
     * @param  array  $parameters The array of parameters from the history record
     *
     * @return string             The string output of the prettified parameters
     */
    public function prettifyParameters(
              $reportUri,
        array $parameters
    ) {
        //Get the report input controls
        try {
            $inputControls = $this->getReportInputControls($reportUri, true);
        } catch (\Exception $e) {
            $out = "";
            foreach ($parameters as $k => $v) {
                foreach ($v as $item) {
                    $out .= $k . ": " . $item . "<br/>";
                }

            }
            return $out;
        }

        //Convert the input controls and the parameters into a string
        $output = [];
        foreach ($inputControls as $key => $inputControl) {
            //If the input control has options or not
            if (null !== $inputControl['options']) {
                //If the parameter has values for the input control
                if (isset($parameters[$key])) {
                    //Foreach selection in the parameters array, convert to the option label
                    $labels = [];
                    foreach ($parameters[$key] as $selection) {
                        if (isset($inputControl['options'][$selection])) {
                            $labels[] = $inputControl['options'][$selection];
                        } else {
                            $labels[] = 'Unrecognized Value';
                        }
                    }

                    //Add to the output
                    $output[] = $inputControl['control']->getLabel() . ': ' . implode(', ', $labels);
                } else {
                    //Show that the selection was blank
                    $output[] = $inputControl['control']->getLabel() . ': ---';
                }
            } else {
                //Check if a value exists in the parameters array
                if (isset($parameters[$key])) {
                    $output[] = $inputControl['control']->getLabel() . ': ' . implode(', ', $parameters[$key]);
                } else {
                    //Check if a default value exists, else mark it as being empty
                    if ($inputControl['control']->getDefaultValue()) {
                        $output[] = $inputControl['control']->getLabel() . ': ' . $inputControl['control']->getDefaultValue();
                    } else {
                        $output[] = $inputControl['control']->getLabel() . ': ---';
                    }
                }
            }
        }

        //Glue the output array together into a string
        return implode('<br/>', $output);
    }

    /**
     * Gets the input controls for the given report
     *   Will stash the return from a call to retrieve from if called again in the history classes lifespan
     *
     * @param  string  $reportUri The uri of the report to get the input controls for
     * @param  boolean $reindex   If true, the input controls will be reindexed to be keyed with their id
     *                              only at the stashing stage
     *
     * @return array              The input controls
     */
    protected function getReportInputControls(
        $reportUri,
        $reindex = true
    ) {
        //Check if the reporturi is in the ic stash
        if (isset($this->inputControlStash[$reportUri])) {
            //Get them from the stash
            $inputControls = $this->inputControlStash[$reportUri];
        } else {
            //Get the input controls
            $inputControls = $this->clientService->getReportInputControls(
                $reportUri, $this->clientService->getDefaultInputOptionsSource());

            //If the rekey is set, rekey em
            if ($reindex) {
                $newArray = [];
                foreach ($inputControls as $ic) {
                    //Create an entry in the new array
                    $newArray[$ic->getId()] = ['control' => $ic];

                    //Get the options, and index them by id => label and add it to the
                    if (method_exists($ic, 'getOptionList')) {
                        foreach ($ic->getOptionList() as $option) {
                            $newArray[$ic->getId()]['options'][$option->getId()] = $option->getLabel();
                        }
                    } else {
                        $newArray[$ic->getId()]['options'] = null;
                    }
                }
                $inputControls = $newArray;
            }

            //Stash them
            $this->inputControlStash[$reportUri] = $inputControls;
        }

        //Return the array of input controls
        return $inputControls;
    }

    /**
     * Load all the recently executed reports
     *
     * @param  boolean $limitByCurrentUser Whether to limit by just the current user or to get all reports
     * @param  array   $options            Options to pass to the history repository's filter method
     *
     * @return array
     */
    public function loadRecentHistory(
        $limitByCurrentUser = true,
        $options = []
    ) {
        //Resitrict by the user if requested
        if ($limitByCurrentUser) {
            $options['username'] = $this->tokenStorage->getToken()->getUsername();
        }

        //Call the repo class and the return the record entries
        return $this->getReportHistoryRepository()->filter($options);
    }

    /**
     * Return the entity manager assigned to handle report history records
     *
     * @return Doctrine\ORM\EntityManager The entity manager used by this service
     */
    public function getEM()
    {
        return $this->doctrine->getEntityManager($this->entityManager);
    }

    /**
     * Returns the report history repository using this services entity manager
     *
     * @return Mesd\Jasper\ReportBundle\Repository\ReportHistoryRepository The report history repo
     */
    public function getReportHistoryRepository()
    {
        return $this->getEM()->getRepository('MesdJasperReportBundle:ReportHistory');
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
