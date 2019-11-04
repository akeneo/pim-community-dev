<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConfigureAxisValueLabelsNormalizerPass implements CompilerPassInterface
{
    private const SERVICE_TAG = 'pim_axis_value_label_normalizer';

    public function process(ContainerBuilder $container)
    {
        $normalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant');

        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);
        foreach ($taggedServices as $serviceId => $taggedService) {
            $normalizer->addArgument($container->getDefinition($serviceId));
        }
    }
}
