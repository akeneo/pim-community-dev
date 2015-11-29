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

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\Model\ChannelInterface;

/**
 * Product asset variation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Variation implements VariationInterface
{
    /** @var int */
    protected $id;

    /** @var ReferenceInterface */
    protected $reference;

    /** @var ChannelInterface */
    protected $channel;

    /** @var FileInfoInterface */
    protected $fileInfo;

    /** @var FileInfoInterface */
    protected $sourceFileInfo;

    /** @var bool */
    protected $locked;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locked = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAsset()
    {
        if (null !== $this->getReference()) {
            return $this->getReference()->getAsset();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        if (null !== $this->getReference()) {
            return $this->getReference()->getLocale();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     */
    public function setReference(ReferenceInterface $reference)
    {
        $this->reference = $reference;
        $reference->addVariation($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileInfo(FileInfoInterface $fileInfo = null)
    {
        $this->fileInfo = $fileInfo;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFileInfo()
    {
        return $this->sourceFileInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceFileInfo(FileInfoInterface $fileInfo = null)
    {
        $this->sourceFileInfo = $fileInfo;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocked($locked)
    {
        $this->locked = (bool) $locked;

        return $this;
    }

    /**
     * Check if a variation can be considered complete
     *
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return bool
     */
    public function isComplete($localeCode, $channelCode)
    {
        $channelOk = (null === $this->getChannel() || $channelCode === $this->getChannel()->getCode());

        $localeOk = (null === $this->getReference()->getLocale()
            || $localeCode === $this->getReference()->getLocale()->getCode());

        $fileOk = null !== $this->getFileInfo();

        return $channelOk && $localeOk && $fileOk;
    }
}
