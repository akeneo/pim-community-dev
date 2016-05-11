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
use Pim\Bundle\EnrichBundle\Filter\ProductValuesEditDataFilter as BaseProductValuesEditDataFilter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Product edit data filter
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductValuesEditDataFilter extends BaseProductValuesEditDataFilter
{
     /**
     * @param ObjectFilterInterface        $objectFilter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LocaleRepositoryInterface    $localeRepository
     * @param ChannelRepositoryInterface   $channelRepository
     */
    public function __construct(
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        parent::__construct($objectFilter, $attributeRepository, $localeRepository, $channelRepository);
    }

    /**
     * Test if a value is accepted or not
     *
     * @param AttributeInterface $attribute
     * @param array              $value
     *
     * @return boolean
     */
    protected function acceptValue(AttributeInterface $attribute, $value, array $options = [])
    {
        if (null !== $value['locale']) {
            $locale = $this->getLocale($value['locale']);
            if (null === $locale) {
                return false;
            }

            if (!$attribute->isLocalizable()) {
                return false;
            }

            if (!$locale->isActivated()) {
                return false;
            }

            if ($attribute->isReadOnly()) {
                return false;
            }

            if ($this->objectFilter->filterObject(
                $this->getLocale($value['locale']),
                'pim.internal_api.locale.edit',
                $options
            )) {
                return false;
            }

            if ($attribute->isLocaleSpecific() && !in_array($value['locale'], $attribute->getLocaleSpecificCodes())) {
                return false;
            }
        }

        if (null !== $value['scope']) {
            if (!$attribute->isScopable()) {
                return false;
            }
            if (null === $this->getChannel($value['scope'])) {
                return false;
            }
        }

        return true;
    }
}
