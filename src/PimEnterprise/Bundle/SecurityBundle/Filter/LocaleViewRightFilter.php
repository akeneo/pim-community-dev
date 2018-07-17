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

use Akeneo\Channel\Component\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Permission\Component\Attributes;

/**
 * Locale filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class LocaleViewRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($locale, $type, array $options = [])
    {
        if (!$this->supportsObject($locale, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "LocaleInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof LocaleInterface;
    }
}
