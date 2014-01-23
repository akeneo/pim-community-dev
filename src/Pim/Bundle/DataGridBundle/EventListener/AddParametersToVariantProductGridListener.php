<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToVariantProductGridListener extends AddParametersToProductGridListener
{
    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = parent::prepareParameters();

        $variantGroupId = $queryParameters['currentGroup'];
        $productIds = $this->productManager
            ->getEntityManager()
            ->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')
            ->getEligibleProductIds($variantGroupId);
        if (count($productIds) === 0) {
            $productIds = [0];
        }
        $queryParameters['productIds'] = $productIds;

        return $queryParameters;
    }
}
