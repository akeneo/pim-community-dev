<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Filter;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Filter\AbstractFilter;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Product Value filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueLocaleRightFilter extends AbstractFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var LocaleManager */
    protected $localeManager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param LocaleManager            $localeManager
     */
    public function __construct(SecurityContextInterface $securityContext, LocaleManager $localeManager)
    {
        $this->securityContext = $securityContext;
        $this->localeManager   = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \Exception('This filter only handles objects of type "ProductValueInterface"');
        }

        return !$productValue->getAttribute()->isLocalizable() ||
            $this->securityContext->isGranted(
                Attributes::VIEW_PRODUCTS,
                $this->localeManager->getLocaleByCode($productValue->getLocale())
            );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }
}
