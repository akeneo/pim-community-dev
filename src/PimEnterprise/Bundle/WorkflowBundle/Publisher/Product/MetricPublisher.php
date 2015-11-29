<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Component\Catalog\Model\MetricInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product metric publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
        return $object instanceof MetricInterface;
    }

    /**
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric
     */
    protected function createNewPublishedProductMetric()
    {
        return new $this->publishClassName();
    }
}
