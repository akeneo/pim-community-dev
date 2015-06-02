<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass to register Metadata builders
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterMetadataBuildersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const METADATA_BUILDER_REGISTRY = 'pimee_product_asset.registry.metadata_builder';

    /** @staticvar string */
    const METADATA_BUILDER_TAG = 'metadata.builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::METADATA_BUILDER_REGISTRY);
        $builders = $container->findTaggedServiceIds(self::METADATA_BUILDER_TAG);

        foreach ($builders as $builderId => $tags) {
            $alias = $tags[0]['alias'];
            $builder = new Reference($builderId);

            $registry->addMethodCall('register', [$builder, $alias]);
        }
    }
}
