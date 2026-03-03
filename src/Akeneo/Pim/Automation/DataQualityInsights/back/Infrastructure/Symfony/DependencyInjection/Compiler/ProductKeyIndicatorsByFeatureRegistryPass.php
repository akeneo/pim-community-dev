<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\DependencyInjection\Compiler;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ProductKeyIndicatorsByFeatureRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductKeyIndicatorsByFeatureRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->findDefinition(ProductKeyIndicatorsByFeatureRegistry::class);
        $keyIndicatorServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.data_quality_insights.compute_product_key_indicator');

        foreach ($keyIndicatorServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $registryDefinition->addMethodCall('register', [new Reference($serviceId), $tag['feature'] ?? null]);
            }
        }
    }
}
