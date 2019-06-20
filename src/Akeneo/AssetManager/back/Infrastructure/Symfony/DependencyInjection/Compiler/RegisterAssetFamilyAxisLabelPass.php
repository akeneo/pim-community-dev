<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RegisterAssetFamilyAxisLabelPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $normalizer = $container->getDefinition('pim_enrich.normalizer.entity_with_family_variant');

        $assetFamilyAxisLabelNormalizer = $container->getDefinition('akeneo_assetmanager.infrastructure.catalog.normalizer.asset_family_axis_label_normalizer');
        $normalizer->addArgument($assetFamilyAxisLabelNormalizer);
    }
}
