<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Model;

use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class CompletenessPerAttributeGroup implements CompletenessPerAttributeGroupInterface
{
    /** @var int */
    private $id;

    /** @var bool */
    private $hasAtLeastOneRequiredAttributeFilled;

    /** @var bool */
    private $complete;

    /** @var LocaleInterface */
    private $locale;

    /** @var ChannelInterface */
    private $channel;

    /** @var ProductInterface */
    private $product;

    /** @var AttributeGroupInterface */
    private $attributeGroup;

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
    public function isHasAtLeastOneRequiredAttributeFilled()
    {
        return $this->hasAtLeastOneRequiredAttributeFilled;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasAtLeastOneRequiredAttributeFilled($hasAtLeastOneRequiredAttributeFilled)
    {
        $this->hasAtLeastOneRequiredAttributeFilled = $hasAtLeastOneRequiredAttributeFilled;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * {@inheritdoc}
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
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
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroup()
    {
        return $this->attributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroup($attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;
    }
}
