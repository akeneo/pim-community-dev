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

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

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
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface'             =>
                'pimee_workflow.entity.published_product.class',
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValueInterface'        =>
                'pimee_workflow.entity.published_product_value.class',
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetricInterface'       =>
                'pimee_workflow.entity.published_product_metric.class',
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPriceInterface'        =>
                'pimee_workflow.entity.published_product_price.class',
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductCompletenessInterface' =>
                'pimee_workflow.entity.published_product_completeness.class',
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociationInterface'  =>
                'pimee_workflow.entity.published_product_association.class',
        ];
    }
}
