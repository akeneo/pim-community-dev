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
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Attribute filter
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class AttributeEditRightFilter extends AbstractAuthorizationFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($attribute, $type, array $options = [])
    {
        if (!$this->supportsObject($attribute, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "AttributeInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof AttributeInterface;
    }
}
