<?php

namespace MESD\Jasper\ReportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
            ->setName('mesd_jasper_report:security:generate-yaml');
            ->setDescription('Generates/Updates the report security yaml file from the jasper server contents')
            ->addOption('roles', 'r', InputOption::VALUE_IS_ARRAY & InputOption::VALUE_OPTIONAL, 'The roles that are set by default', 'ROLE_USER');
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The path where the report security yml file will be placed at')
        ;
    }

    /**
     * Generate the base security yaml file to be used by the client
     *
     * @param  InputInterface  $input  The input interface
     * @param  OutputInterface $output The output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

    }
}