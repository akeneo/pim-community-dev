<?php

namespace Response\Bundle\BigQueryProductImportBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class BigQueryProductImportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('archiving.yml');
        $loader->load('jobs.yml');
        $loader->load('job_parameters.yml');
        $loader->load('steps.yml');
        $loader->load('readers.yml');
    }
}
