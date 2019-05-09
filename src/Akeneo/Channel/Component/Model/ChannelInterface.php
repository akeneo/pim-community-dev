<?php

namespace Akeneo\Channel\Component\Model;

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
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return ChannelInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @return ChannelInterface
     */
    public function setLabel($label);

    /**
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * @param CategoryInterface $category
     *
     * @return ChannelInterface
     */
    public function setCategory(CategoryInterface $category);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCurrencies();

    /**
     * @param array $currencies
     */
    public function setCurrencies(array $currencies);

    /**
     * @param CurrencyInterface $currency
     *
     * @return ChannelInterface
     */
    public function addCurrency(CurrencyInterface $currency);

    /**
     * @param CurrencyInterface $currency
     *
     * @return ChannelInterface
     */
    public function removeCurrency(CurrencyInterface $currency);

    /**
     * @param CurrencyInterface $currency
     *
     * @return boolean
     */
    public function hasCurrency(CurrencyInterface $currency);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLocales();

    /**
     * @param array $locales
     */
    public function setLocales(array $locales);

    /**
     * @param LocaleInterface $locale
     *
     * @return ChannelInterface
     */
    public function addLocale(LocaleInterface $locale);

    /**
     * @param LocaleInterface $locale
     *
     * @return ChannelInterface
     */
    public function removeLocale(LocaleInterface $locale);

    /**
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    public function hasLocale(LocaleInterface $locale);

    /**
     * @param array $conversionUnits
     *
     * @return ChannelInterface
     */
    public function setConversionUnits(array $conversionUnits);

    /**
     * @return array
     */
    public function getConversionUnits();

    /**
     * Get locale codes
     *
     * @return array
     */
    public function getLocaleCodes();

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
