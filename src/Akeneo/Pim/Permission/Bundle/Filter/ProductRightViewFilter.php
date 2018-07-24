<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;

/**
 * Product view filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductRightViewFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($product, $type, array $options = [])
    {
        if (!$this->supportsObject($product, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ProductInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW, $product);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ProductInterface;
    }
}
