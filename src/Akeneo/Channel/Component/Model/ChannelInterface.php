<?php

namespace Akeneo\Channel\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Channel\Component\Event\ChannelEvent;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

/**
 * Channel interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelInterface extends ReferableInterface, VersionableInterface, TranslatableInterface
{
    public function getId(): int;

    public function getCode(): string;

    /**
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Channel\Component\Model\ChannelInterface;

    public function getLabel(): string;

    /**
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Channel\Component\Model\ChannelInterface;

    public function getCategory(): \Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;

    /**
     * @param CategoryInterface $category
     */
    public function setCategory(CategoryInterface $category): \Akeneo\Channel\Component\Model\ChannelInterface;

    public function getCurrencies(): ArrayCollection;

    /**
     * @param array $currencies
     */
    public function setCurrencies(array $currencies);

    /**
     * @param CurrencyInterface $currency
     */
    public function addCurrency(CurrencyInterface $currency): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * @param CurrencyInterface $currency
     */
    public function removeCurrency(CurrencyInterface $currency): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * @param CurrencyInterface $currency
     */
    public function hasCurrency(CurrencyInterface $currency): bool;

    public function getLocales(): ArrayCollection;

    /**
     * @param array $locales
     */
    public function setLocales(array $locales);

    /**
     * @param LocaleInterface $locale
     */
    public function addLocale(LocaleInterface $locale): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * @param LocaleInterface $locale
     */
    public function removeLocale(LocaleInterface $locale): \Akeneo\Channel\Component\Model\ChannelInterface;

    /**
     * @param LocaleInterface $locale
     */
    public function hasLocale(LocaleInterface $locale): bool;

    /**
     * @param array $conversionUnits
     */
    public function setConversionUnits(array $conversionUnits): \Akeneo\Channel\Component\Model\ChannelInterface;

    public function getConversionUnits(): array;

    /**
     * Get locale codes
     */
    public function getLocaleCodes(): array;

    /**
     * To string
     *
     * @return string
     */
    public function __toString();

    /**
     * @return ChannelEvent[]
     */
    public function popEvents(): array;
}
