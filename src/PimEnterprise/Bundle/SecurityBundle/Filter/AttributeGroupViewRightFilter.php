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

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Attribute group filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeGroupViewRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($attributeGroup, $type, array $options = [])
    {
        if (!$this->supportsObject($attributeGroup, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "AttributeGroupInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof AttributeGroupInterface;
    }
}
