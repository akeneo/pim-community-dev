<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Compiler pass to register job templates configurations
 *
 * @author    Samir Boulil<samir.boulil@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterJobTemplatePass implements CompilerPassInterface
{
    /** @staticvar string The registry id */
    const PROVIDER_ID = 'pim_import_export.provider.job_template';

    /** @staticvar string */
    const PROVIDER_CONFIG_PARAMETER = 'pim_import_export.job_template.config';

    /** @var NodeInterface */
    protected $jobTemplateConfig;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configRootPath = sprintf('%sResources%sconfig', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $jobTemplatesFilePath = sprintf('%s%sjob_templates.yml', $configRootPath, DIRECTORY_SEPARATOR);

        // retrieve each job config from bundles
        $jobTemplatesConfig = array();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . $jobTemplatesFilePath)) {
                // merge job configs
                if (empty($jobTemplatesConfig)) {
                    $jobTemplatesConfig = Yaml::parse(file_get_contents(realpath($file)));
                } else {
                    $entities = Yaml::parse(file_get_contents(realpath($file)));
                    foreach ($entities['job_templates'] as $jobName => $jobFileConfig) {
                        // merge result with already existing job templates to add new job templates definition
                        if (isset($jobTemplatesConfig['job_templates'][$jobName])) {
                            $jobTemplatesConfig['job_templates'][$jobName] = array_replace_recursive(
                                $jobTemplatesConfig['job_templates'][$jobName],
                                $jobFileConfig
                            );
                        } else {
                            $jobTemplatesConfig[$jobName] = $jobFileConfig;
                        }
                    }
                }
            }
        }

        // process configurations to validate and merge
        $config = $this->processConfig($jobTemplatesConfig);

        // load service
        $configPath = sprintf('%s%s..%s..%s', __DIR__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $configRootPath);
        $loader = new YamlFileLoader($container, new FileLocator($configPath));
        $loader->load('services.yml');

        // set job templates config
        $container->setParameter(static::PROVIDER_CONFIG_PARAMETER, $config);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function processConfig(array $config)
    {
        $processor = new Processor();
        if (!$this->jobTemplateConfig) {
            $this->jobTemplateConfig = $this->getJobTemplatesConfigTree();
        }

        return $processor->process($this->jobTemplateConfig, $config);
    }

    /**
     * @return NodeInterface
     */
    protected function getJobTemplatesConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('job_templates');
        $root
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('templates')
                        ->children()
                            ->scalarNode('show')->end()
                            ->scalarNode('edit')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}
