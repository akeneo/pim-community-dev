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

use Pim\Bundle\CatalogBundle\Filter\AbstractFilter;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Locale filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class LocaleViewRightFilter extends AbstractFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($locale, $type, array $options = [])
    {
        if (!$locale instanceof LocaleInterface) {
            throw new \LogicException('This filter only handles objects of type "LocaleInterface"');
        }

        return !$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof LocaleInterface;
    }
}
