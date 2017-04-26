<?php

namespace Acme\Bundle\XmlConnectorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class AcmeXmlConnectorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('archiving.yml');
        $loader->load('jobs.yml');
        $loader->load('job_defaults.yml');
        $loader->load('steps.yml');
        $loader->load('readers.yml');
        $loader->load('writers.yml');
    }
}
