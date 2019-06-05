<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RegisterReferenceEntityAxisLabelPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $normalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant');

        $referenceEntityAxisLabelNormalizer = $container->getDefinition('akeneo_referenceentity.infrastructure.catalog.normalizer.reference_entity_axis_label_normalizer');
        $normalizer->addArgument($referenceEntityAxisLabelNormalizer);
    }
}
