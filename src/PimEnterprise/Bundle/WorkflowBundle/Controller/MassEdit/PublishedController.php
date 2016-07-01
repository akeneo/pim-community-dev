<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\MassEdit;

use Pim\Bundle\EnrichBundle\Controller\MassEdit\AbstractMassEditController;

/**
 * Mass Edit controller implementation for published products.
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedController extends AbstractMassEditController
{
    /**
     * {@inheritdoc}
     */
    protected function getGridName()
    {
        return 'published-product-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationRoute()
    {
        return 'pimee_workflow_mass_edit_published_action_configure';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOperationTemplate($operationAlias)
    {
        return sprintf('PimEnterpriseWorkflowBundle:MassEditAction:published/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationTemplate()
    {
        return 'PimEnterpriseWorkflowBundle:MassEditAction:published/choose.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationTemplate($operationAlias)
    {
        return sprintf('PimEnrichBundle:MassEditAction:published/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationRedirectRoute()
    {
        return 'pimee_workflow_published_product_index';
    }
}
