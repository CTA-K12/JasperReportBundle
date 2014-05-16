<?php

namespace MESD\Jasper\ReportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSecurityYamlCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure() {
        $this
            ->setName('mesd_jasper_report:security:generate-yaml')
            ->setDescription('Generates/Updates the report security yaml file from the jasper server contents')
            ->addOption('roles', 'r', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The roles that are set by default will use the roles defined in the configuration if none given')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The path where the report security yml file will be placed at')
            ->addOption('backup', 'b', InputOption::VALUE_NONE, 'Backup the current report security yaml if it exists')
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Maximum depth to go down in the folder tree')
        ;
    }

    /**
     * Generate the base security yaml file to be used by the client
     *
     * @param  InputInterface  $input  The input interface
     * @param  OutputInterface $output The output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        //Start by getting the security service reference
        $security = $this->getContainer()->get('mesd.jasperreport.security');

        //Process the input options
        $roles  = $input->getOption('roles')  ?: $security->getDefaultRoles();
        $backup = $input->getOption('backup') ? true : false;
        $path   = $input->getOption('path')   ?: $security->getSecurityFile();
        $depth  = intval($input->getOption('depth'))  ?: -1;

        //Init the security service (normally done automatically via the setRole and canView)
        $security->init();

        //Backup the existing file if the backup flag is set and the existing file exists
        if ($backup && $security->isLoadedFromFile()) {
            //change the existing path to add that it is a backup
            $currentPath = explode('/', $security->getSecurityFile());
            $currentPath[count($currentPath) - 1] = '~' . $currentPath[count($currentPath) - 1];
            $security->saveSecurityConfiguration(implode('/', $currentPath), true);
        }

        //Turn off security for the client for the time being
        $this->getContainer()->get('mesd.jasperreport.client')->setUseSecurity(false);

        //Convert the file structure to an array with the roles set
        $this->setResource($security, $security->getDefaultFolder(), $roles, $depth);

        //Save the security configuration
        $security->saveSecurityConfiguration($path, true);
    }


    protected function setResource($security, $resource, $roles, $depth) {
        //Do not go beyond the max depth
        if (0 == $depth) {
            return; 
        }

        //Set the resource if it is not yet set
        if (!$security->checkIfExists($resource)) {
            //Set the config
            $security->setRoles($resource, $roles);
        }

        //Get the children 
        $children = $this->getContainer()->get('mesd.jasperreport.client')->getResourceList($resource);
        
        //Call recursively
        foreach($children as $child) {
            $this->setResource($security, $child->getUriString(), $roles, $depth - 1);
        }

        //return
        return;
    }
}