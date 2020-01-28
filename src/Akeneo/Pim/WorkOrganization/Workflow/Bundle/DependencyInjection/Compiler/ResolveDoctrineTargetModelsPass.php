<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociationInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductUniqueDataInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine target models mapping
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ResolveDoctrineTargetModelsPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping()
    {
        return [
            PublishedProductInterface::class => 'pimee_workflow.entity.published_product.class',
            PublishedProductAssociationInterface::class => 'pimee_workflow.entity.published_product_association.class',
            PublishedProductUniqueDataInterface::class => 'pimee_workflow.entity.published_product_unique_data.class',
        ];
    }
}
