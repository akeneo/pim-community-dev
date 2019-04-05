<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ConfigureAxisValueLabelsNormalizerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $normalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant');

        $simpleSelectLabelNormalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant.simple_select.label.normalizer');
        $normalizer->addArgument($simpleSelectLabelNormalizer);

        $metricLabelNormalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant.metric.label.normalizer');
        $normalizer->addArgument($metricLabelNormalizer);
    }
}
