<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Manager;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Resolves expected values for attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValuesResolver implements AttributeValuesResolverInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ChannelInterface[] */
    protected $channels;

    /** @var LocaleInterface[] */
    protected $locales;

    /**
     * @param ChannelRepositoryInterface $channelRepository Channel repository
     * @param LocaleRepositoryInterface  $localeRepository  Locale repository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Resolves an array of values that are expected to link product to an attribute depending on locale and scope
     * Each value is returned as an array with 'attribute', 'type', 'scope' and 'locale' keys
     *
     * @param AttributeInterface[] $attributes Attributes to resolve
     * @param ChannelInterface[]   $channels   Context channels (all channels by default)
     * @param LocaleInterface[]    $locales    Context locales (all locales by default)
     *
     * @return array:array
     */
    public function resolveEligibleValues(array $attributes, array $channels = null, array $locales = null) : array
    {
        $this->channels = $channels;
        $this->locales  = $locales;

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
                $requiredValues[] = [
                    'attribute' => $attribute->getCode(),
                    'type'      => $attribute->getType(),
                    'locale'    => null,
                    'scope'     => null
                ];
            }
            $expectedValues = $this->filterExpectedValues($attribute, $requiredValues);
            foreach ($expectedValues as $expectedValue) {
                $values[] = $expectedValue;
            }
        }

        return $values;
    }

    /**
     * Filter expected values based on the locales available for the provided attribute
     *
     * @param AttributeInterface $attribute
     * @param array                                                    $values
     *
     * @return array
     */
    protected function filterExpectedValues(AttributeInterface $attribute, array $values) : array
    {
        if ($attribute->isLocaleSpecific()) {
            $availableLocales = $attribute->getAvailableLocaleCodes();
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
    protected function getLocaleRows(AttributeInterface $attribute) : array
    {
        $locales = $this->getLocales();
        $localeRows = [];
        foreach ($locales as $locale) {
            $localeRows[] = [
                'attribute' => $attribute->getCode(),
                'type'      => $attribute->getType(),
                'locale'    => $locale->getCode(),
                'scope'     => null
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
    protected function getScopeRows(AttributeInterface $attribute) : array
    {
        $channels = $this->getChannels();
        $scopeRows = [];
        foreach ($channels as $channel) {
            $scopeRows[] = [
                'attribute' => $attribute->getCode(),
                'type'      => $attribute->getType(),
                'locale'    => null,
                'scope'     => $channel->getCode()
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
    protected function getScopeToLocaleRows(AttributeInterface $attribute) : array
    {
        $channels = $this->getChannels();
        $scopeToLocaleRows = [];
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $scopeToLocaleRows[] = [
                    'attribute' => $attribute->getCode(),
                    'type'      => $attribute->getType(),
                    'locale'    => $locale->getCode(),
                    'scope'     => $channel->getCode()
                ];
            }
        }

        return $scopeToLocaleRows;
    }

    /**
     * @return ChannelInterface[]
     */
    protected function getChannels() : array
    {
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }

        return $this->channels;
    }

    /**
     * @return LocaleInterface[]
     */
    protected function getLocales() : array
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->getActivatedLocales();
        }

        return $this->locales;
    }
}
