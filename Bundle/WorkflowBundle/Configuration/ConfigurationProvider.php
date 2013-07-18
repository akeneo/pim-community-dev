<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class ConfigurationProvider
{
    const WORKFLOW_ROOT_NODE = 'workflows';

    /**
     * @var string
     */
    protected $configDirectory = '/Resources/config/';

    /**
     * @var string
     */
    protected $configFilePattern = 'workflow.yml';

    /**
     * @var array
     */
    protected $kernelBundles = array();

    /**
     * @param array $kernelBundles
     */
    public function __construct(array $kernelBundles)
    {
        $this->kernelBundles = $kernelBundles;
    }

    /**
     * @return WorkflowDefinition[]
     * @throws InvalidConfigurationException
     */
    public function getWorkflowDefinitions()
    {
        $configDirectories = $this->getConfigDirectories();

        $finder = new Finder();
        $finder->in($configDirectories)->name($this->configFilePattern);

        $treeNode = $this->getConfigurationTreeBuilder()->buildTree();

        $workflowDefinitions = array();
        /** @var $file \SplFileInfo */
        foreach ($finder as $file) {
            $configData = Yaml::parse($file->getRealPath());
            if (empty($configData[self::WORKFLOW_ROOT_NODE])) {
                continue;
            }

            try {
                $finalizedData = $treeNode->finalize($configData[self::WORKFLOW_ROOT_NODE]);
            } catch (InvalidConfigurationException $exception) {
                $message = sprintf(
                    'Can\'t parse workflow configuration from %s. %s',
                    $file->getRealPath(),
                    $exception->getMessage()
                );
                throw new InvalidConfigurationException($message);
            }

            $workflowDefinitions = array_merge(
                $workflowDefinitions,
                $this->buildWorkflowDefinitions($finalizedData)
            );
        }

        return $workflowDefinitions;
    }

    /**
     * @return array
     */
    protected function getConfigDirectories()
    {
        $configDirectory = str_replace('/', DIRECTORY_SEPARATOR, $this->configDirectory);
        $configDirectories = array();

        foreach ($this->kernelBundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $bundleConfigDirectory = dirname($reflection->getFilename()) . $configDirectory;
            if (is_dir($bundleConfigDirectory) && is_readable($bundleConfigDirectory)) {
                $configDirectories[] = realpath($bundleConfigDirectory);
            }
        }

        return $configDirectories;
    }

    /**
     * @return TreeBuilder
     */
    protected function getConfigurationTreeBuilder()
    {
        // TODO Define full workflow configuration format
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::WORKFLOW_ROOT_NODE);
        $rootNode
            ->prototype('array')
            ->children()
                ->scalarNode('label')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('enabled')
                    ->defaultTrue()
                ->end()
                ->scalarNode('start_step')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('managed_entity_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param array $finalizedData
     * @return WorkflowDefinition[]
     */
    protected function buildWorkflowDefinitions($finalizedData)
    {
        $workflowDefinitions = array();
        foreach ($finalizedData as $workflowName => $workflowConfiguration) {
            $workflowDefinition = new WorkflowDefinition();
            $workflowDefinition
                ->setName($workflowName)
                ->setLabel($workflowConfiguration['label'])
                ->setEnabled($workflowConfiguration['enabled'])
                ->setStartStep($workflowConfiguration['start_step'])
                ->setManagedEntityClass($workflowConfiguration['managed_entity_class'])
                ->setConfiguration($workflowConfiguration);

            $workflowDefinitions[] = $workflowDefinition;
        }

        return $workflowDefinitions;
    }
}
