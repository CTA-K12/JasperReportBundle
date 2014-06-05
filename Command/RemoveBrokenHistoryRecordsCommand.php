<?php

namespace Mesd\Jasper\ReportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveBrokenHistoryRecordsCommand extends ContainerAwareCommand
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const MAX_RECORDS_IN_MEMORY = 1000;

    /////////////////////////
    // IMPLEMENTED METHODS //
    /////////////////////////


    /**
     * Configure the command
     */
    protected function configure() {
        $this
            ->setName('mesd_jasper_report:history:delete_broken_records')
            ->setDescription('Removes any history record that points to a report that is no longer in the report store')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dont make any database changes, just display the records that will be deleted')
        ;
    }


    /**
     * Generate the base security yaml file to be used by the client
     *
     * @param  InputInterface  $input  The input interface
     * @param  OutputInterface $output The output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        //Turn off the time limit and save the current max to restore it when done
        $timeLimit = ini_get('max_execution_time');
        set_time_limit(0);

        //Change the loader path in order to account for being in the root directory of the project instead of the web
        $path = $this->getContainer()->get('kernel')->getRootDir() . '/../web/';
        $relative = $this->getContainer()->get('mesd.jasper.report.loader')->getReportCacheDir();
        $this->getContainer()->get('mesd.jasper.report.loader')->setReportCacheDir($path . $relative);

        //Set the option flags and get the entity manager
        $dryrun = $input->getOption('dry-run') ? true : false;

        //Get the entity manager and the history repo that the history service is using
        $em = $this->getContainer()->get('mesd.jasper.report.history')->getEM();
        $repo = $this->getContainer()->get('mesd.jasper.report.history')->getReportHistoryRepository();

        //Since there can be a lot of records in the history, we'll only load a set amount at a time, so first lets get a count
        //First get the total number of records in the database
        $total = $repo->getTotalCount();
        $limit = self::MAX_RECORDS_IN_MEMORY;
        $page = 0;
        $output->writeln('Checking ' . $total . ' records...');

        //While there are records to grab
        while(($page * $limit) < $total) {
            //Get the records
            $records = $repo->filter(array('page' => $page, 'limit' => $limit));

            //For each records check if its report exists in the store, and delete it if it isnt
            foreach($records as $record) {
                if ($record->getRequestId() 
                    && $this->getContainer()->get('mesd.jasper.report.loader')->getReportLoader()->checkIfReportIsStored($record->getRequestId())) {
                    //Do nothing for now the record is good
                } else {
                    $output->writeln('Record [' . $record->getId() . '] with request id [' . $record->getRequestId() . '] is no longer valid');
                    if (!$dryrun) {
                        $em->remove($record);
                    }
                }
            }

            //Clear out the records from mem
            if (!$dryrun) {
                $em->flush();
            }
            $em->clear();

            //Increment page
            $page++;
        }

        //Write a complete message for some reason
        $output->writeln('Done!');

        //Reset the time limit
        set_time_limit($timeLimit);
    }
}