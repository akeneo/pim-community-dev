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

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product value publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ValuePublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var PublisherInterface */
    protected $publisher;

    /**
     * @param string             $publishClassName
     * @param PublisherInterface $publisher
     */
    public function __construct($publishClassName, PublisherInterface $publisher)
    {
        $this->publishClassName = $publishClassName;
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $publishedValue = $this->createNewPublishedProductValue();
        $publishedValue->setAttribute($object->getAttribute());
        $publishedValue->setLocale($object->getLocale());
        $publishedValue->setScope($object->getScope());

        $originalData = $object->getData();
        $copiedData = null;
        $options = ['product' => $object->getEntity(), 'value' => $object];

        if ($originalData instanceof \Doctrine\Common\Collections\Collection) {
            if (count($originalData) > 0) {
                $copiedData = new ArrayCollection();
                foreach ($originalData as $object) {
                    $copiedObject = $this->publisher->publish($object, $options);
                    $copiedData->add($copiedObject);
                }
            }
        } elseif (is_object($originalData)) {
            $copiedData = $this->publisher->publish($originalData, $options);
        } else {
            $copiedData = $originalData;
        }

        if ($copiedData) {
            $publishedValue->setData($copiedData);
        }

        return $publishedValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductValueInterface;
    }

    /**
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValueInterface
     */
    protected function createNewPublishedProductValue()
    {
        return new $this->publishClassName();
    }
}
