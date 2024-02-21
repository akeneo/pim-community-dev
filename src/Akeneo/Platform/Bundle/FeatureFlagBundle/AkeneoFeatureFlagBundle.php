<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * AkeneoFeatureFlagBundle bundle
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoFeatureFlagBundle extends Bundle implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $configs = [];
        foreach ($container->findTaggedServiceIds('feature_flags.is_enabled') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->setPublic(true);
            $container->setDefinition("$id.real", $definition);
            $container->setDefinition($id, (new Definition())->setSynthetic(true));

            $configs[$id] = current($tags);

            if (!empty($configs[$id]['otherwise'])) {
                $container->getDefinition($configs[$id]['otherwise'])->setPublic(true);
            }
        }
        $container->setParameter('feature_flagged.services', $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        foreach ($this->container->getParameter('feature_flagged.services') as $id => $config) {
            $isEnabled = $this->container->get('feature_flags')->isEnabled($config['feature']);
            if ($isEnabled) {
                $this->container->set($id, $this->container->get("$id.real"));
            } elseif (!empty($config['otherwise'])) {
                $this->container->set($id, $this->container->get($config['otherwise']));
            }
        }
    }
}
