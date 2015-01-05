<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Channel interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelInterface extends ReferableInterface, VersionableInterface
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
     * @param Currency $currency
     *
     * @return ChannelInterface
     */
    public function addCurrency(Currency $currency);

    /**
     * @param Currency $currency
     *
     * @return ChannelInterface
     */
    public function removeCurrency(Currency $currency);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLocales();

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
     * @return string
     */
    public function getColor();

    /**
     * @param string $color
     *
     * @return ChannelInterface
     */
    public function setColor($color);
}
