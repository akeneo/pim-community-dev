<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class ConfigurationProvider
{
    const NODE_WORKFLOWS = 'workflows';

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
     * @var ConfigurationTree
     */
    protected $configurationTree;

    /**
     * @param array $kernelBundles
     * @param ConfigurationTree $configurationTree
     */
    public function __construct(array $kernelBundles, ConfigurationTree $configurationTree)
    {
        $this->kernelBundles = $kernelBundles;
        $this->configurationTree = $configurationTree;
    }

    /**
     * @return array
     * @throws InvalidConfigurationException
     */
    public function getWorkflowDefinitionConfiguration()
    {
        $configDirectories = $this->getConfigDirectories();

        $finder = new Finder();
        $finder->in($configDirectories)->name($this->configFilePattern);

        $treeNode = $this->getConfigurationTree();

        $configuration = array();
        /** @var $file \SplFileInfo */
        foreach ($finder as $file) {
            $realPathName = $file->getRealPath();
            $configData = Yaml::parse($realPathName);
            if (empty($configData[self::NODE_WORKFLOWS])) {
                continue;
            }

            try {
                $finalizedData = $treeNode->finalize($configData[self::NODE_WORKFLOWS]);
            } catch (InvalidConfigurationException $exception) {
                $message = sprintf(
                    'Can\'t parse workflow configuration from %s. %s',
                    $realPathName,
                    $exception->getMessage()
                );
                throw new InvalidConfigurationException($message);
            }

            foreach ($finalizedData as $workflowName => $workflowConfiguration) {
                if (isset($configuration[$workflowName])) {
                    throw new InvalidConfigurationException(
                        sprintf('Duplicated workflow name "%s" in %s', $workflowName, $realPathName)
                    );
                }

                $configuration[$workflowName] = $workflowConfiguration;
            }
        }

        return $configuration;
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
     * @return NodeInterface
     */
    protected function getConfigurationTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_WORKFLOWS);
        /** @var NodeBuilder $nodeBuilder */
        $nodeBuilder = $rootNode
            ->prototype('array')
                ->children()
                    // workflow parameters
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('start_step')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('managed_entity_class')
                        ->defaultNull()
                    ->end();

        foreach ($this->configurationTree->getNodeDefinitions() as $nodeDefinition) {
            $nodeBuilder->append($nodeDefinition);
        }

        return $treeBuilder->buildTree();
    }
}
