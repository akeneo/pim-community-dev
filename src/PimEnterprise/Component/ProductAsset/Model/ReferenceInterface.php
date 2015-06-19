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

/**
 * Product asset reference interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ReferenceInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return AssetInterface
     */
    public function getAsset();

    /**
     * @param AssetInterface $asset
     *
     * @return ReferenceInterface
     */
    public function setAsset(AssetInterface $asset);

    /**
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @param LocaleInterface $locale
     *
     * @return ReferenceInterface
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * @return FileInterface
     */
    public function getFile();

    /**
     * @param FileInterface $file
     *
     * @return ReferenceInterface
     */
    public function setFile(FileInterface $file = null);

    /**
     * @return ArrayCollection|VariationInterface[]
     */
    public function getVariations();

    /**
     * @param ArrayCollection $variations
     *
     * @return ReferenceInterface
     */
    public function setVariations(ArrayCollection $variations);

    /**
     * @param VariationInterface $variation
     *
     * @return ReferenceInterface
     */
    public function addVariation(VariationInterface $variation);

    /**
     * @param VariationInterface $variation
     *
     * @return ReferenceInterface
     */
    public function removeVariation(VariationInterface $variation);

    /**
     * @param ChannelInterface $channel
     *
     * @return VariationInterface|null
     */
    public function getVariation(ChannelInterface $channel);

    /**
     * @param ChannelInterface $channel
     *
     * @return bool
     */
    public function hasVariation(ChannelInterface $channel);
}
