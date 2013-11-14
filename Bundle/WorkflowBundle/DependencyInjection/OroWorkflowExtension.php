<?php

namespace Oro\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OroWorkflowExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('prototypes.yml');
        $loader->load('conditions.yml');
        $loader->load('actions.yml');
        $loader->load('assemblers.yml');
        $loader->load('form_types.yml');
        $loader->load('serializer.yml');
        $loader->load('entity_binder.yml');
        $loader->load('configuration.yml');
        $loader->load('twig_extensions.yml');
        $loader->load('listener.yml');
        $loader->load('http.yml');
        $loader->load('validator.yml');
    }
}
