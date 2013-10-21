<?php

namespace Oro\Bundle\DistributionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroDistributionExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');

        $bundles = array_merge($container->getParameter('assetic.bundles'), array(
            'OroAsseticBundle',
            'OroAddressBundle',
            'OroUIBundle',
            'OroUserBundle',
            'OroGridBundle',
            'OroFilterBundle',
            'OroNavigationBundle',
            'OroWindowsBundle',
            'OroSegmentationTreeBundle',
            'OroEntityExtendBundle',
            'OroSecurityBundle',
            'JDareClankBundle',
        ));

        $templates = array_merge($container->getParameter('twig.form.resources'), array(
            'OroUIBundle:Form:fields.html.twig',
            'OroFormBundle:Form:fields.html.twig',
            'OroTranslationBundle:Form:fields.html.twig',
            'OroAddressBundle:Include:fields.html.twig',
            'OroOrganizationBundle:Form:fields.html.twig',
            'OroSecurityBundle:Form:fields.html.twig',
        ));

        $container->setParameter('twig.form.resources', $templates);
        $container->setParameter('assetic.bundles', $bundles);
    }
}
