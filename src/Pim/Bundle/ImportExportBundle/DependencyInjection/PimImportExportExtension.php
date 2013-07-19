<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportExtension extends Extension
{
    private $factory;

    public function __construct()
    {
        $this->factory = new ReferenceFactory;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('serializer.yml');
        $loader->load('import_export.yml');

        $container->setParameter('pim_serializer.encoder.csv.delimiter', $config['encoders']['csv']['delimiter']);
        $container->setParameter('pim_serializer.encoder.csv.enclosure', $config['encoders']['csv']['enclosure']);
        $container->setParameter('pim_serializer.encoder.csv.with_header', $config['encoders']['csv']['with_header']);

        $this->createExporterServices($config, $container);
    }

    private function createExporterServices(array $config, ContainerBuilder $container)
    {
        $registry = $container->getDefinition('pim_import_export.exporter_registry');
        foreach ($config['exporters'] as $alias => $exportConfig) {

            $def = new Definition(
                'Pim\\Bundle\\ImportExportBundle\\Exporter',
                array(
                    $this->factory->createReference('pim_serializer'),
                    $this->factory->createReference(
                        $this->createService(sprintf('%s_reader', $alias), $exportConfig['reader'], $container)
                    ),
                    $this->factory->createReference(
                        $this->createService(sprintf('%s_writer', $alias), $exportConfig['writer'], $container)
                    ),
                    $exportConfig['format']
                )
            );
            $def->setPublic(false);

            $registry->addMethodCall('registerExporter', array($alias, $def));
        }
    }

    private function createService($suffix, array $config, ContainerBuilder $container)
    {
        $def = new Definition;
        $def->setClass($config['type']);
        $def->setArguments($this->resolveReferences($config['options']));

        $id  = sprintf('pim_import_export.%s', $suffix);

        $container->setDefinition($id, $def);

        return $id;
    }

    private function resolveReferences(array $options)
    {
        foreach ($options as $index => $option) {
            if (is_string($option)) {
                if (0 === strpos($option, '@')) {
                    $options[$index] = $this->factory->createReference(substr($option, 1));
                }
            }
        }

        return $options;
    }
}
