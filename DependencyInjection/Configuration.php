<?php

namespace MESD\Jasper\ReportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('mesd_jasper_report')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_folder')->defaultValue('/reports')->end()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('username')->defaultValue('please_change')->end()
                        ->scalarNode('password')->defaultValue('please_change')->end()
                        ->scalarNode('server')->defaultValue('please_change')->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('use_cache')->defaultTrue()->end()
                        ->scalarNode('cache_dir')->defaultValue('app/cache')->end()
                        ->scalarNode('cache_timeout')->defaultValue(30)->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $builder;
    }
}