<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Published product event
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class PublishedProductEvent extends Event
{
    /** @var ProductInterface */
    protected $product;

    /** @var PublishedProductInterface */
    protected $publishedProduct;

    /**
     * The constructor
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    public function __construct(ProductInterface $product, PublishedProductInterface $published = null)
    {
        $this->product = $product;
        $this->publishedProduct = $published;
    }

    /**
     * @param ProductInterface $product
     *
     * @return PublishedProductEvent
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param PublishedProductInterface $published
     *
     * @return PublishedProductEvent
     */
    public function setPublishedProduct($published)
    {
        $this->publishedProduct = $published;

        return $this;
    }

    /**
     * @return PublishedProductInterface
     */
    public function getPublishedProduct()
    {
        return $this->publishedProduct;
    }
}
