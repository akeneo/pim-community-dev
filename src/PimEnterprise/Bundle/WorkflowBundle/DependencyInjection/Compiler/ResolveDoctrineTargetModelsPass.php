<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

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
            'PimEnterprise\Component\Workflow\Model\PublishedProductInterface'             => 'pimee_workflow.entity.published_product.class',
            'PimEnterprise\Component\Workflow\Model\PublishedProductCompletenessInterface' => 'pimee_workflow.entity.published_product_completeness.class',
            'PimEnterprise\Component\Workflow\Model\PublishedProductAssociationInterface'  => 'pimee_workflow.entity.published_product_association.class',
            'PimEnterprise\Component\Workflow\Model\PublishedProductUniqueDataInterface'   => 'pimee_workflow.entity.published_product_unique_data.class',
        ];
    }
}
