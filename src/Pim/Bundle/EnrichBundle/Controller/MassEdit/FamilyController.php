<?php

namespace Pim\Bundle\EnrichBundle\Controller\MassEdit;

/**
 * Mass Edit controller implementation for families.
 * Handle all the steps from choosing action to run to the launching of the action.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyController extends AbstractMassEditController
{
    /**
     * {@inheritdoc}
     */
    protected function getGridName()
    {
        return 'family-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationRoute()
    {
        return 'pim_enrich_mass_edit_family_action_configure';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOperationTemplate($operationAlias)
    {
        return sprintf('PimEnrichBundle:MassEditAction:family/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationTemplate()
    {
        return 'PimEnrichBundle:MassEditAction:family/choose.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationTemplate($operationAlias)
    {
        return sprintf('PimEnrichBundle:MassEditAction:family/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationRedirectRoute()
    {
        return 'pim_enrich_family_index';
    }
}
