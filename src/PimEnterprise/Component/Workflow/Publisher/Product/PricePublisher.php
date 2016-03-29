<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Publisher\Product;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;

/**
 * Product price publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PricePublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /**
     * @param string $publishClassName
     */
    public function __construct($publishClassName)
    {
        $this->publishClassName = $publishClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $copiedPrice = $this->createNewPublishedProductPrice();
        $copiedPrice->setData($object->getData());
        $copiedPrice->setCurrency($object->getCurrency());

        return $copiedPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductPriceInterface;
    }

    /**
     * @return \PimEnterprise\Component\Workflow\Model\PublishedProductPrice
     */
    protected function createNewPublishedProductPrice()
    {
        return new $this->publishClassName();
    }
}
