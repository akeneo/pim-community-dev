<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Model\AbstractProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice;

/**
 * Product price publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductPricePublisher implements PublisherInterface
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
        $copiedPrice = new $this->publishClassName();
        $copiedPrice = new PublishedProductPrice();
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
}
