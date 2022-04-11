<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\DependencyInjection\Compiler;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\ReadCriteriaRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterReadCriteriaPass  implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $isFeatureDqiFull = $container->get('feature_flags')->isEnabled('data_quality_insights_full');
        $registryDefinition = $container->findDefinition(ReadCriteriaRegistry::class);
        $criterionServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.data_quality_insights.evaluate_product_criterion');

        foreach ($criterionServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                // Keep the tags with the feature "dqi_full_only" only if the feature is enabled
                if (!isset($tag['feature']) || ($tag['feature'] === 'dqi_full_only' && $isFeatureDqiFull)) {
                    $registryDefinition->addMethodCall('register', [new Reference($serviceId)]);
                }
            }
        }
    }
}
