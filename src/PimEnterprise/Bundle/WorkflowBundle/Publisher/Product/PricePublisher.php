<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Model\AbstractProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product price publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return $object instanceof AbstractProductPrice;
    }

    /**
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice
     */
    protected function createNewPublishedProductPrice()
    {
        return new $this->publishClassName();
    }
}
