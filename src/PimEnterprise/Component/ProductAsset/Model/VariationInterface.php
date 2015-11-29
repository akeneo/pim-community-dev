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

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

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
     * @return FileInfoInterface
     */
    public function getFileInfo();

    /**
     * @param FileInfoInterface $fileInfo
     *
     * @return VariationInterface
     */
    public function setFileInfo(FileInfoInterface $fileInfo = null);

    /**
     * @return FileInfoInterface
     */
    public function getSourceFileInfo();

    /**
     * @param FileInfoInterface $fileInfo
     *
     * @return VariationInterface
     */
    public function setSourceFileInfo(FileInfoInterface $fileInfo = null);

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

    /**
     * Check if a variation can be considered complete
     *
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return bool
     */
    public function isComplete($localeCode, $channelCode);
}
