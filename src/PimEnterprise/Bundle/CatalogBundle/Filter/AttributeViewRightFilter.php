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
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeViewRightFilter extends AbstractAuthorizationFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var array */
    protected $authorizations = [];

    /**
     * {@inheritdoc}
     */
    public function filterObject($attribute, $type, array $options = [])
    {
        if (!$this->supportsObject($attribute, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "AttributeInterface"');
        }

        $group = $attribute->getGroup();
        $key = $group->getId();

        if (!isset($this->authorizations[$key])) {
            $this->authorizations[$key] = $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group);
        }

        return !$this->authorizations[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof AttributeInterface;
    }
}
