<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Product Value filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueAttributeGroupRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        foreach ($collection as $productValue) {
            if ($this->filterObject($productValue, $type, $options)) {
                $collection->remove($productValue);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$this->supportsObject($productValue, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        return !$this->authorizationChecker->isGranted(
            Attributes::VIEW_ATTRIBUTES,
            $productValue->getAttribute()->getGroup()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof ProductValueCollectionInterface && null !== $this->tokenStorage->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ProductValueInterface;
    }
}
