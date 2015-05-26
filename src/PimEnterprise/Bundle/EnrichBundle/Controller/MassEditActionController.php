<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Pim\Bundle\EnrichBundle\Controller\MassEditActionController as BaseMassEditActionController;

/**
 * Mass edit operation controller
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class MassEditActionController extends BaseMassEditActionController
{
    /**
     * Override to retrieve items name from published product grid
     *
     * {@inheritdoc}
     */
    protected function getItemsName($gridName)
    {
        $itemsName = parent::getItemsName($gridName);

        if ('published-product-grid' === $gridName) {
            $itemsName = 'published_product';
        }

        return $itemsName;
    }
}
