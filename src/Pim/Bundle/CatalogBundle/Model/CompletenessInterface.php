<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Product completeness interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessInterface
{
    /**
     * Getter required count
     *
     * @return int
     */
    public function getRequiredCount();

    /**
     * Setter locale
     *
     * @param LocaleInterface $locale
     *
     * @return CompletenessInterface
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * Getter ratio
     *
     * @return int
     */
    public function getRatio();

    /**
     * Setter missing count
     *
     * @param int $missingCount
     *
     * @return CompletenessInterface
     */
    public function setMissingCount($missingCount);

    /**
     * Setter channel
     *
     * @param ChannelInterface $channel
     *
     * @return CompletenessInterface
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * Setter product
     *
     * @param ProductInterface $product
     *
     * @return CompletenessInterface
     */
    public function setProduct(ProductInterface $product);

    /**
     * Getter locale
     *
     * @return Locale
     */
    public function getLocale();

    /**
     * Getter channel
     *
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * Getter missing count
     *
     * @return int
     */
    public function getMissingCount();

    /**
     * Getter product
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Setter required count
     *
     * @param int $requiredCount
     *
     * @return CompletenessInterface
     */
    public function setRequiredCount($requiredCount);

    /**
     * Setter ratio
     *
     * @param int $ratio
     *
     * @return CompletenessInterface
     */
    public function setRatio($ratio);
}
