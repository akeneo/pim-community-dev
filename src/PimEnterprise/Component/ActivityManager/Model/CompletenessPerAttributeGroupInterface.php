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
interface CompletenessPerAttributeGroupInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return bool
     */
    public function isHasAtLeastOneRequiredAttributeFilled();

    /**
     * @param bool $inProgress
     */
    public function setHasAtLeastOneRequiredAttributeFilled($inProgress);

    /**
     * @return bool
     */
    public function isComplete();

    /**
     * @param bool $complete
     */
    public function setComplete($complete);

    /**
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @param LocaleInterface $locale
     */
    public function setLocale($locale);

    /**
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     */
    public function setChannel($channel);

    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @param ProductInterface $product
     */
    public function setProduct($product);

    /**
     * @return AttributeGroupInterface
     */
    public function getAttributeGroup();

    /**
     * @param AttributeGroupInterface $attributeGroup
     */
    public function setAttributeGroup($attributeGroup);
}
