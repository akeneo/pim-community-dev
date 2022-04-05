<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProductEvaluationCriteriaPass  implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $isFeatureDqiFull = $container->get('feature_flags')->isEnabled('data_quality_insights_full');
        $registryDefinition = $container->findDefinition('akeneo.pim.automation.data_quality_insights.product_criteria_evaluation_registry');
        $criterionServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.data_quality_insights.evaluate_product_criterion');

        foreach ($criterionServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['feature']) || ($tag['feature'] === 'dqi_full_only' && $isFeatureDqiFull)) {
                    $registryDefinition->addMethodCall('register', [new Reference($serviceId)]);
                }
            }
        }
    }
}
