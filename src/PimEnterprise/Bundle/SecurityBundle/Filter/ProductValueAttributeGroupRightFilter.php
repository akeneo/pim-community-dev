<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
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
    public function filterObject($value, $type, array $options = [])
    {
        if (!$this->supportsObject($value, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ValueInterface"');
        }

        return !$this->authorizationChecker->isGranted(
            Attributes::VIEW_ATTRIBUTES,
            $value->getAttribute()->getGroup()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof ValueCollectionInterface && null !== $this->tokenStorage->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ValueInterface;
    }
}
