<?php

namespace Pim\Bundle\EnrichBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Product edit data filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesEditDataFilter implements CollectionFilterInterface
{
    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeInterface[] */
    protected $attributes = [];

    /** @var LocaleInterface[] */
    protected $locales = [];

    /** @var ChannelInterface[] */
    protected $channels = [];

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
        $this->objectFilter = $objectFilter;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($valuesData, $type, array $options = [])
    {
        $filteredValues = [];
        foreach ($valuesData as $attributeCode => $values) {
            $attribute = $this->getAttribute($attributeCode);
            if (null !== $attribute && !$this->objectFilter->filterObject(
                $attribute,
                'pim.internal_api.attribute.edit',
                $options
            )) {
                $filteredValues[$attributeCode] = $this->getFilteredValues($attribute, $values, $options);
            }
        }

        return array_filter($filteredValues);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * Generate filtered values for the given attribute
     *
     * @param AttributeInterface $attribute
     * @param array              $values
     * @param array              $options
     *
     * @return array
     */
    protected function getFilteredValues(AttributeInterface $attribute, array $values, array $options = [])
    {
        $filteredValues = [];

        foreach ($values as $value) {
            if ($this->acceptValue($attribute, $value, $options)) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
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

    /**
     * @param string $code
     *
     * @return AttributeInterface
     */
    protected function getAttribute($code)
    {
        if (!array_key_exists($code, $this->attributes)) {
            $this->attributes[$code] = $this->attributeRepository->findOneByIdentifier($code);
        }

        return $this->attributes[$code];
    }

    /**
     * @param string $code
     *
     * @return LocaleInterface
     */
    protected function getLocale($code)
    {
        if (!array_key_exists($code, $this->locales)) {
            $this->locales[$code] = $this->localeRepository->findOneByIdentifier($code);
        }

        return $this->locales[$code];
    }

    /**
     * @param string $code
     *
     * @return ChannelInterface
     */
    protected function getChannel($code)
    {
        if (!array_key_exists($code, $this->channels)) {
            $this->channels[$code] = $this->channelRepository->findOneByIdentifier($code);
        }

        return $this->channels[$code];
    }
}
