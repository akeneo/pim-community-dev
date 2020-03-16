<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\ProductEditDataFilter as BaseProductEditFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;

/**
 * Enterprise override to add product ownership check for product classification
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductEditDataFilter extends BaseProductEditFilter
{
    /**
     * {@inheritdoc}
     */
    protected function isAllowedToClassify(EntityWithValuesInterface $product): bool
    {
        $hasAcl = parent::isAllowedToClassify($product);

        return $hasAcl && $this->securityFacade->isGranted(Attributes::OWN, $product);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedToUpdateAssociations(EntityWithValuesInterface $product): bool
    {
        $hasAcl = parent::isAllowedToUpdateAssociations($product);

        return $hasAcl && $this->securityFacade->isGranted(Attributes::OWN, $product);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedToUpdateStatus(EntityWithValuesInterface $product): bool
    {
        $hasAcl = parent::isAllowedToUpdateStatus($product);

        return $hasAcl && $this->securityFacade->isGranted(Attributes::OWN, $product);
    }
}
