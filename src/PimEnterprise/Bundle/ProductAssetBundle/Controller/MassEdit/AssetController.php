<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller\MassEdit;

use Pim\Bundle\EnrichBundle\Controller\MassEdit\AbstractMassEditController;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetController extends AbstractMassEditController
{
    /**
     * {@inheritdoc}
     */
    protected function getGridName()
    {
        return 'asset-grid';
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationRoute()
    {
        return 'pimee_product_asset_mass_edit_asset_action_configure';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigureOperationTemplate($operationAlias)
    {
        return sprintf('PimEnterpriseProductAssetBundle:MassEditAction:asset/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getChooseOperationTemplate()
    {
        return 'PimEnterpriseProductAssetBundle:MassEditAction:asset/choose.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationTemplate($operationAlias)
    {
        return sprintf('MassEditAction/configure/%s.html.twig', $operationAlias);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPerformOperationRedirectRoute()
    {
        return 'pimee_product_asset_index';
    }
}
