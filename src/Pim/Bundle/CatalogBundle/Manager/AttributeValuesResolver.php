<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Resolve expected values for attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValuesResolver
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository Channel repository
     * @param LocaleRepositoryInterface  $localeRepository  Locale repository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository  = $localeRepository;
    }

    /**
     * Returns an array of values that are expected to link product to an attribute depending on locale and scope
     * Each value is returned as an array with 'scope' and 'locale' keys
     *
     * @param AttributeInterface[] $attributes
     *
     * @return array:array
     */
    public function getEligibleValues(array $attributes)
    {
        $values = [];
        foreach ($attributes as $attribute) {
            $requiredValues = [];
            if ($attribute->isScopable() && $attribute->isLocalizable()) {
                $requiredValues = $this->getScopeToLocaleRows($attribute);
            } elseif ($attribute->isScopable()) {
                $requiredValues = $this->getScopeRows($attribute);
            } elseif ($attribute->isLocalizable()) {
                $requiredValues = $this->getLocaleRows($attribute);
            } else {
                $requiredValues[] = ['attribute' => $attribute->getCode(), 'locale' => null, 'scope' => null];
            }
            $values = array_merge($values, $this->filterExpectedValues($attribute, $requiredValues));
        }

        return $values;
    }

    /**
     * Filter expected values based on the locales available for the provided attribute
     *
     * @param AttributeInterface $attribute
     * @param array              $values
     *
     * @return array
     */
    protected function filterExpectedValues(AttributeInterface $attribute, array $values)
    {
        if ($attribute->isLocaleSpecific()) {
            $availableLocales = $attribute->getLocaleSpecificCodes();
            foreach ($values as $index => $value) {
                if ($value['locale'] && !in_array($value['locale'], $availableLocales)) {
                    unset($values[$index]);
                }
            }
        }

        return $values;
    }

    /**
     * Return rows for available locales
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getLocaleRows(AttributeInterface $attribute)
    {
        $locales = $this->localeRepository->getActivatedLocales();
        $localeRows = [];
        foreach ($locales as $locale) {
            $localeRows[] = [
                'attribute' => $attribute->getCode(), 'locale' => $locale->getCode(), 'scope' => null
            ];
        }

        return $localeRows;
    }

    /**
     * Return rows for available channels
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getScopeRows(AttributeInterface $attribute)
    {
        $channels = $this->channelRepository->findAll();
        $scopeRows = [];
        foreach ($channels as $channel) {
            $scopeRows[] = [
                'attribute' => $attribute->getCode(), 'locale' => null, 'scope' => $channel->getCode()
            ];
        }

        return $scopeRows;
    }

    /**
     * Return rows for available channels and theirs locales
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getScopeToLocaleRows(AttributeInterface $attribute)
    {
        $channels = $this->channelRepository->findAll();
        $scopeToLocaleRows = [];
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $scopeToLocaleRows[] = [
                    'attribute' => $attribute->getCode(),
                    'locale' => $locale->getCode(),
                    'scope' => $channel->getCode()
                ];
            }
        }

        return $scopeToLocaleRows;
    }
}
