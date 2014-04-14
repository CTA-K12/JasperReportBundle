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

        //Set the cache settings
        $reportClientDefinition->addMethodCall('setReportUseCache', array($config['cache']['use_cache']));
        $reportClientDefinition->addMethodCall('setReportCacheDir', array($config['cache']['cache_dir']));
        $reportClientDefinition->addMethodCall('setReportCacheTimeout', array($config['cache']['cache_timeout']));

        //Set the input control settings
        $reportClientDefinition->addMethodCall('setOptionHandlerServiceName', array($config['options_handler']));

        //Set the default folder
        $reportClientDefinition->addMethodCall('setReportDefaultFolder', array($config['default_folder']));

        //Set the presentation settings
        $reportClientDefinition->addMethodCall('setDefaultAssetRoute', array($config['routing']['defaultAssetRoute']));

        //Connect to the server
        $reportClientDefinition->addMethodCall('connect');
    }

    public function getAlias() {
        return 'mesd_jasper_report';
    }
}
