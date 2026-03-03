<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriteriaByFeatureRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processForProducts($container);
        $this->processForProductModels($container);
    }

    private function processForProducts(ContainerBuilder $container): void
    {
        $registryDefinition = $container->findDefinition('akeneo.pim.automation.data_quality_insights.product_criteria_by_feature_registry');
        $criterionServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.data_quality_insights.evaluate_product_criterion');

        foreach ($criterionServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $registryDefinition->addMethodCall('register', [new Reference($serviceId), $tag['feature'] ?? null]);
            }
        }
    }

    private function processForProductModels(ContainerBuilder $container): void
    {
        $registryDefinition = $container->findDefinition('akeneo.pim.automation.data_quality_insights.product_model_criteria_by_feature_registry');
        $criterionServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.data_quality_insights.evaluate_product_model_criterion');

        foreach ($criterionServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $registryDefinition->addMethodCall('register', [new Reference($serviceId), $tag['feature'] ?? null]);
            }
        }
    }
}
