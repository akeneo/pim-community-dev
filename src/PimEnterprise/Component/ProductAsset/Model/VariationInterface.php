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
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

/**
 * Product asset variation interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface VariationInterface
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
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @return ReferenceInterface
     */
    public function getReference();

    /**
     * @param ReferenceInterface $reference
     *
     * @return VariationInterface
     */
    public function setReference(ReferenceInterface $reference);

    /**
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     *
     * @return VariationInterface
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * @return FileInterface
     */
    public function getFile();

    /**
     * @param FileInterface $file
     *
     * @return VariationInterface
     */
    public function setFile(FileInterface $file = null);

    /**
     * @return FileInterface
     */
    public function getSourceFile();

    /**
     * @param FileInterface $file
     *
     * @return VariationInterface
     */
    public function setSourceFile(FileInterface $file = null);

    /**
     * @return bool
     */
    public function isLocked();

    /**
     * @param bool $locked
     *
     * @return VariationInterface
     */
    public function setLocked($locked);
}
