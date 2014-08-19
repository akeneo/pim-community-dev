<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Model\AbstractMetric;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product metric publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricPublisher implements PublisherInterface
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
        $copiedMetric = $this->createNewPublishedProductMetric();
        $copiedMetric->setData($object->getData());
        $copiedMetric->setBaseData($object->getBaseData());
        $copiedMetric->setUnit($object->getUnit());
        $copiedMetric->setBaseUnit($object->getBaseUnit());
        $copiedMetric->setFamily($object->getFamily());

        return $copiedMetric;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AbstractMetric;
    }

    /**
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric
     */
    protected function createNewPublishedProductMetric()
    {
        return new $this->publishClassName();
    }
}
