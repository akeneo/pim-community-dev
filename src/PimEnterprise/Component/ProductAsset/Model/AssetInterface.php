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

use Akeneo\Component\FileStorage\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Component\Classification\CategoryAwareInterface;
use Pim\Component\Classification\TagAwareInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Product asset interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface AssetInterface extends ReferenceDataInterface, TagAwareInterface, CategoryAwareInterface
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
     * @return AssetInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return AssetInterface
     */
    public function setDescription($description);

    /**
     * @return ReferenceInterface[]|ArrayCollection of ReferenceInterface
     */
    public function getReferences();

    /**
     * @return LocaleInterface[]|ArrayCollection of LocaleInterface
     */
    public function getLocales();

    /**
     * @param ArrayCollection of ReferenceInterface $references
     *
     * @return AssetInterface
     */
    public function setReferences(ArrayCollection $references);

    /**
     * @param ReferenceInterface $reference
     *
     * @return AssetInterface
     */
    public function addReference(ReferenceInterface $reference);

    /**
     * @param ReferenceInterface $reference
     *
     * @return AssetInterface
     */
    public function removeReference(ReferenceInterface $reference);

    /**
     * @param LocaleInterface|null $locale
     *
     * @return ReferenceInterface|null
     */
    public function getReference(LocaleInterface $locale = null);

    /**
     * @param LocaleInterface|null $locale
     *
     * @return bool
     */
    public function hasReference(LocaleInterface $locale = null);

    /**
     * @return VariationInterface[]
     */
    public function getVariations();

    /**
     * @param ChannelInterface     $channel
     * @param LocaleInterface|null $locale
     *
     * @return VariationInterface|null
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
     * @return \DateTime
     */
    public function getEndOfUseAt();

    /**
     * @param \DateTime|null $endOfUseAt
     *
     * @return AssetInterface
     */
    public function setEndOfUseAt($endOfUseAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $createdAt
     *
     * @return AssetInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updatedAt
     *
     * @return AssetInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * Look for the variation corresponding to the specified channel and locale and return its file.
     *
     * @param ChannelInterface     $channel
     * @param LocaleInterface|null $locale
     *
     * @return FileInterface|null
     */
    public function getFileForContext(ChannelInterface $channel, LocaleInterface $locale = null);

    /**
     * @return bool
     */
    public function isLocalizable();
}
