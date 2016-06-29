<?php

namespace Pim\Bundle\EnrichBundle\Controller\MassEdit;

/**
 * Mass Edit controller implementation for products.
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductController extends AbstractMassEditController
{
    /**
     * {@inheritdoc}
     */
    protected function getGridName()
    {
        return 'product-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationRoute()
    {
        return 'pim_enrich_mass_edit_product_action_configure';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOperationTemplate($operationAlias)
    {
        return sprintf('PimEnrichBundle:MassEditAction:product/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationTemplate()
    {
        return 'PimEnrichBundle:MassEditAction:product/choose.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationTemplate($operationAlias)
    {
        return sprintf('PimEnrichBundle:MassEditAction:product/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationRedirectRoute()
    {
        return 'return pim_enrich_product_index';
    }
}
