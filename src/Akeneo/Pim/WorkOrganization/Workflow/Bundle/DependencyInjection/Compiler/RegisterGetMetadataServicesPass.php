<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterGetMetadataServicesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('akeneo.pim.enrichment.product.connector.get_product_from_identifiers')) {
            return;
        }

        $definition = $container->getDefinition('akeneo.pim.enrichment.product.connector.get_product_from_identifiers');
        $metadata = $container->getDefinition('pimee_workflow.query.get_metadata_for_product');

        $definition->replaceArgument('$getMetadata', $metadata);

        if (!$container->hasDefinition('akeneo.pim.enrichment.product.connector.get_product_models_from_codes')) {
            return;
        }

        $definition = $container->getDefinition('akeneo.pim.enrichment.product.connector.get_product_models_from_codes');
        $metadata = $container->getDefinition('pimee_workflow.query.get_metadata_for_product_model');

        $definition->replaceArgument('$getMetadata', $metadata);
    }
}
