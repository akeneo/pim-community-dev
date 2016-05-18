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
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Product values edit data filter
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductValuesEditDataFilter implements ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($attribute, $type, array $options = [])
    {
        if (!$this->supportsObject($attribute, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "AttributeInterface"');
        }

        return (bool) $attribute->getProperty('is_read_only');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof AttributeInterface;
    }
}
