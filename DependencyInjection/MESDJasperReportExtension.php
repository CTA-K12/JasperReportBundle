<?php

namespace MESD\Jasper\ReportBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MESDJasperReportExtension extends Extension
{
    public function load( array $configs, ContainerBuilder $container ) {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yml' );

        $reportClientDefinition = $container->getDefinition('mesd.jasperreport.client');

        //Set the connection settings from the config file
        $reportClientDefinition->addMethodCall('setReportUsername', array($config['connection']['username']));
        $reportClientDefinition->addMethodCall('setReportPassword', array($config['connection']['password']));
        $reportClientDefinition->addMethodCall('setReportHost', array($config['connection']['host']));
        $reportClientDefinition->addMethodCall('setReportPort', array($config['connection']['port']));

        //Set the report cache settings
        $reportClientDefinition->addMethodCall('setReportCacheDir', array($config['report_cache']['cache_dir']));

        //Set the resource list cache settings
        $reportClientDefinition->addMethodCall('setUseFolderCache', array($config['folder_cache']['use_cache']));
        $reportClientDefinition->addMethodCall('setFolderCacheDir', array($config['folder_cache']['cache_dir']));
        $reportClientDefinition->addMethodCall('setFolderCacheTimeout', array($config['folder_cache']['cache_timeout']));

        //Set the input control settings
        $reportClientDefinition->addMethodCall('setOptionHandlerServiceName', array($config['options_handler']));

        //Set the default folder
        $reportClientDefinition->addMethodCall('setDefaultFolder', array($config['default_folder']));

        //Set the presentation settings
        $reportClientDefinition->addMethodCall('setDefaultAssetRoute', array($config['routing']['defaultAssetRoute']));

        //Connect to the server
        $reportClientDefinition->addMethodCall('connect');
    }

    public function getAlias() {
        return 'mesd_jasper_report';
    }
}
