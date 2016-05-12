<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Filter\ProductValuesEditDataFilter as BaseProductValuesEditDataFilter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Product edit data filter
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductValuesEditDataFilter extends BaseProductValuesEditDataFilter
{
    /**
     * {@inheritdoc}
     */
    protected function acceptValue(AttributeInterface $attribute, $value, array $options = [])
    {
        if ($attribute->isReadOnly()) {
            return false;
        }

        return parent::acceptValue($attribute, $value, $options);
    }
}
