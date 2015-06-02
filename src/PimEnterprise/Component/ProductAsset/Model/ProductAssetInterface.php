<?php

/**
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
     * @return ArrayCollection
     */
    public function getReferences();

    /**
     * @param ArrayCollection $references
     *
     * @return ProductAssetInterface
     */
    public function setReferences(ArrayCollection $references);

    /**
     * @param ProductAssetReferenceInterface $reference
     *
     * @return ProductAssetInterface
     */
    public function addReference(ProductAssetReferenceInterface $reference);

    /**
     * @param ProductAssetReferenceInterface $reference
     *
     * @return ProductAssetInterface
     */
    public function removeReference(ProductAssetReferenceInterface $reference);

    /**
     * @param LocaleInterface|null $locale
     *
     * @return ProductAssetReferenceInterface|null
     */
    public function getReference(LocaleInterface $locale = null);

    /**
     * @param LocaleInterface|null $locale
     *
     * @return bool
     */
    public function hasReference(LocaleInterface $locale = null);

    /**
     * @return ProductAssetVariationInterface[]
     */
    public function getVariations();

    /**
     * @param ChannelInterface     $channel
     * @param LocaleInterface|null $locale
     *
     * @return ProductAssetVariationInterface|null
     */
    public function getVariation(ChannelInterface $channel, LocaleInterface $locale = null);

    /**
     * @param ChannelInterface     $channel
     * @param LocaleInterface|null $locale
     *
     * @return bool
     */
    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale = null);

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
