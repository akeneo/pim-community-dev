<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Model\AbstractMetric;

/**
 * Product metric publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMetricPublisher implements PublisherInterface
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
        $copiedMetric = new $this->publishClassName();
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
}
