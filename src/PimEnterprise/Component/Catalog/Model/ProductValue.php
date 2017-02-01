<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue as BaseProductValue;

/**
 * Enterprise override of the Community product value
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductValue extends BaseProductValue implements ProductValueInterface
{
    /** @var ArrayCollection */
    protected $assets;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param mixed              $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $data)
    {
        parent::__construct($attribute, $channel, $locale, $data);

        $this->assets = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * {@inheritdoc}
     */
    protected function setAssets(ArrayCollection $assets)
    {
        $this->assets = $assets;

        return $this;
    }
}
