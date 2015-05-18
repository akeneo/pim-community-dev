<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Product asset interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductAssetInterface extends ReferenceDataInterface
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
     * @return ProductAssetInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return ProductAssetInterface
     */
    public function setDescription($description);

    /**
     * @return FileInterface
     */
    public function getReference();

    /**
     * @param FileInterface $reference
     *
     * @return ProductAssetInterface
     */
    public function setReference(FileInterface $reference);

    /**
     * @return ArrayCollection
     */
    public function getVariations();

    /**
     * @param ArrayCollection $variations
     *
     * @return ProductAssetInterface
     */
    public function setVariations(ArrayCollection $variations);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function addVariation(ProductAssetVariationInterface $variation);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function removeVariation(ProductAssetVariationInterface $variation);

    /**
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return ProductAssetVariationInterface|null
     */
    public function getVariation(ChannelInterface $channel, LocaleInterface $locale);

    /**
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return bool
     */
    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale);

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param bool $isEnabled
     */
    public function setEnabled($isEnabled);

    /**
     * @return \Datetime
     */
    public function getEndOfUseAt();

    /**
     * @param \Datetime $endOfUseAt
     *
     * @return ProductAssetInterface
     */
    public function setEndOfUseAt(\Datetime $endOfUseAt);

    /**
     * @return \Datetime
     */
    public function getCreatedAt();

    /**
     * @param \Datetime $createdAt
     *
     * @return ProductAssetInterface
     */
    public function setCreatedAt(\Datetime $createdAt);

    /**
     * @return \Datetime
     */
    public function getUpdatedAt();

    /**
     * @param \Datetime $updatedAt
     *
     * @return ProductAssetInterface
     */
    public function setUpdatedAt(\Datetime $updatedAt);
}
