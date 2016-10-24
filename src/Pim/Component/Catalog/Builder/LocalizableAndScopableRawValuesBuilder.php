<?php

namespace Pim\Component\Catalog\Builder;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Builds missing localizable and scopable raw values.
 *
 * This fix is not needed for 1.6 as the frontend correctly handles localizable and scopable values.
 * For example, even if the description en_US mobile does not exist in database,
 * the frontend will be displayed correctly. That's not the case in 1.5. Everything's broken.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class LocalizableAndScopableRawValuesBuilder
{
    /** @var CachedObjectRepositoryInterface */
    private $attributeRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var LocaleInterface[] */
    private $locales;

    /** @var ChannelInterface[] */
    private $channels;

    /**
     * @param CachedObjectRepositoryInterface $attributeRepository
     * @param ChannelRepositoryInterface      $channelRepository
     * @param LocaleRepositoryInterface       $localeRepository
     */
    public function __construct(
        CachedObjectRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Build missing scopable and/or localizable raw values.
     *
     * For instance if you have 2 channels "print", "ecommerce" and 2 locale "en_US", "fr_FR".
     * The attributes "description" is localizable and scopable, "weight" is scopable.
     *
     * Imagine you have the following $rawValues as input:
     *
     *      "description": [
     *          {
     *              "locale": "en_US",
     *              "scope": "print",
     *              "data": "just a description for print",
     *          },
     *          {
     *              "locale": "en_US",
     *              "scope": "ecommerce",
     *              "data": "just a description for ecommerce",
     *          }
     *      ],
     *      "weight": [
     *          {
     *              "locale": "en_US",
     *              "scope": "print",
     *              "data": "6 kg for print",
     *          },
     *      ]
     *
     * The output would be:
     *
     *      "description": [
     *          {
     *              "locale": "en_US",
     *              "scope": "print",
     *              "data": "just a description for print",
     *          },
     *          {
     *              "locale": "en_US",
     *              "scope": "ecommerce",
     *              "data": "just a description for ecommerce",
     *          },
     *          {
     *              "locale": "fr_FR",
     *              "scope": "print",
     *              "data": null,
     *          },
     *          {
     *              "locale": "fr_FR",
     *              "scope": "ecommerce",
     *              "data": null,
     *          },
     *      ],
     *      "weight": [
     *          {
     *              "locale": "en_US",
     *              "scope": "print",
     *              "data": "6 kg for print",
     *          },
     *          {
     *              "locale": "en_US",
     *              "scope": "ecommerce",
     *              "data": null,
     *          },
     *      ]
     *
     * @param array $rawValues
     *
     * @return array
     */
    public function addMissing(array $rawValues)
    {
        $this->fetchChannelsAndLocales();

        foreach ($rawValues as $attributeCode => $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if ($attribute->isScopable() && $attribute->isLocalizable()) {
                $rawValues[$attributeCode] = array_merge(
                    $rawValues[$attributeCode],
                    $this->createMissingLocalizableAndScopable($attribute, $rawValues)
                );
            } elseif ($attribute->isScopable()) {
                $rawValues[$attributeCode] = array_merge(
                    $rawValues[$attributeCode],
                    $this->createMissingScopable($attribute, $rawValues)
                );
            } elseif ($attribute->isLocalizable()) {
                $rawValues[$attributeCode] = array_merge(
                    $rawValues[$attributeCode],
                    $this->createMissingLocalizable($attribute, $rawValues)
                );
            }
        }

        return $rawValues;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $rawValues
     *
     * @return array
     */
    private function createMissingLocalizableAndScopable(AttributeInterface $attribute, array $rawValues)
    {
        $missings = [];

        foreach ($this->channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                if (!$this->hasRawValue($attribute, $rawValues, $channel, $locale)) {
                    $missings[] = $this->createEmptyRawValue($channel, $locale);
                }
            }
        }

        return $missings;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $rawValues
     *
     * @return array
     */
    private function createMissingScopable(AttributeInterface $attribute, array $rawValues)
    {
        $missings = [];

        foreach ($this->channels as $channel) {
            if (!$this->hasRawValue($attribute, $rawValues, $channel, null)) {
                $missings[] = $this->createEmptyRawValue($channel, null);
            }
        }

        return $missings;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $rawValues
     *
     * @return array
     */
    private function createMissingLocalizable(AttributeInterface $attribute, array $rawValues)
    {
        $missings = [];

        foreach ($this->locales as $locale) {
            if (!$this->hasRawValue($attribute, $rawValues, null, $locale)) {
                $missings[] = $this->createEmptyRawValue(null, $locale);
            }
        }

        return $missings;
    }

    /**
     * Create an empty raw value for the given channel and locale with the following format:
     *  [
     *      "locale" => "en_US"
     *      "scope" => "ecommerce"
     *      "data" => null
     *  ]
     *
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return array
     */
    private function createEmptyRawValue(
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $channelCode = null !== $channel ? $channel->getCode() : null;
        $localeCode = null !== $locale ? $locale->getCode() : null;

        return [
            'locale' => $localeCode,
            'scope'  => $channelCode,
            'data'   => null
        ];
    }

    /**
     * Check if the array $rawValues contains the value for the given
     * attribute, locale and channel.
     *
     * @param AttributeInterface $attribute
     * @param array $rawValues
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null $locale
     *
     * @return bool
     */
    private function hasRawValue(
        AttributeInterface $attribute,
        array $rawValues,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        if (!isset($rawValues[$attribute->getCode()])) {
            return false;
        }

        $channelCode = null !== $channel ? $channel->getCode() : null;
        $localeCode = null !== $locale ? $locale->getCode() : null;

        foreach ($rawValues[$attribute->getCode()] as $rawValueAttribute) {
            if ($localeCode === $rawValueAttribute['locale'] && $channelCode === $rawValueAttribute['scope']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch all activated channels and locales. If the JS can do it, why not us huh :p?
     */
    private function fetchChannelsAndLocales()
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->getActivatedLocales();
        }
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }
    }
}
