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

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

/**
 * Product asset variation
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductAssetVariation implements ProductAssetVariationInterface
{
    /** @var int */
    protected $id;

    /** @var ProductAssetReferenceInterface */
    protected $reference;

    /** @var ChannelInterface */
    protected $channel;

    /** @var FileInterface */
    protected $file;

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
    public function setReference(ProductAssetReferenceInterface $reference)
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
    public function getFile()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(FileInterface $file)
    {
        $this->file = $file;

        return $this;
    }
}
