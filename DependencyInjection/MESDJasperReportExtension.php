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

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
        $loader->load( 'services.yml' );

        // $reportClientDefinition = $container->getDefinition('mesd.jasperreport.client');
        // $reportClientDefinition->addMethodCall('setFunTimeString', var_dump($config));
    }

    public function getAlias() {
        return 'mesd_jasper_report';
    }

    public function getNamespace() {
        return 'MESDJasperReportBundle';
    }
}
