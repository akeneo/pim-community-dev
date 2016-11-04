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
        foreach ($rawValues as $attributeCode => $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            $rawValues[$attributeCode] = $this->createMissingValues($attribute, $rawValues[$attributeCode]);
        }

        return $rawValues;
    }

    private function createMissingValues(AttributeInterface $attribute, array $rawValues)
    {
        $locales  = $attribute->isLocalizable() ? $this->getLocales() : [null];
        $channels = $attribute->isScopable() ? $this->getChannels() : [null];

        foreach ($channels as $channel) {
            foreach ($locales as $locale) {
                $localeInChannel  = null === $channel ||
                    null === $locale ||
                    in_array($locale, $channel->getLocales()->toArray());
                $localeInSpecific = !$attribute->isLocaleSpecific() ||
                    null === $locale ||
                    in_array($locale->getCode(), $attribute->getLocaleSpecificCodes());
                $alreadyExist = $this->alreadyExist($rawValues, $locale, $channel);

                if ($localeInChannel && $localeInSpecific && !$alreadyExist) {
                    $rawValues[] = $this->createEmptyRawValue($channel, $locale);
                }
            }
        }

        return $rawValues;
    }

    /**
     * Check if a value already exist in the collection
     *
     * @param array  $rawValues
     * @param string $locale
     * @param string $channel
     *
     * @return boolean
     */
    private function alreadyExist($rawValues, $locale, $channel)
    {
        foreach ($rawValues as $value) {
            if ($value['scope'] === (null !== $channel ? $channel->getCode() : null) &&
                $value['locale'] === (null !== $locale ? $locale->getCode() : null)
            ) {
                return true;
            }
        }

        return false;
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
     * Get locales only if they are not in cache (done right this time, by a javascript guy :3)
     *
     * @return Locales[]
     */
    private function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->getActivatedLocales();
        }

        return $this->locales;
    }

    /**
     * Get the list of channels
     *
     * @return Channel[]
     */
    private function getChannels()
    {
        if (null === $this->channels) {
            $this->channels = $this->channelRepository->findAll();
        }

        return $this->channels;
    }
}
