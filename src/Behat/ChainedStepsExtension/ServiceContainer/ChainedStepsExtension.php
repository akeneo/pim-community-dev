<?php

/*
 * This file is part of the Behat ChainedStepsExtension.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\ChainedStepsExtension\ServiceContainer;

use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\ChainedStepsExtension\Tester\SubStepTester;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ChainedStepsExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'chained_steps';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(SubStepTester::class, array(
            new Reference(TesterExtension::STEP_TESTER_ID),
        ));
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, array('priority' => 100));
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.substep', $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
