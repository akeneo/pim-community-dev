<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric;

/**
 * Product value publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductValuePublisher implements PublisherInterface
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
        $publishedValue = new $this->publishClassName();
        $publishedValue->setAttribute($object->getAttribute());
        $publishedValue->setLocale($object->getLocale());
        $publishedValue->setScope($object->getScope());

        $originalData = $object->getData();
        $copiedData = null;

        if ($originalData instanceof \Doctrine\Common\Collections\Collection) {
            if (count($originalData) > 0) {
                $copiedData = new ArrayCollection();
                foreach ($originalData as $object) {
                    if ($object instanceof ProductPrice) {
                        $copiedObject = new PublishedProductPrice();
                        $copiedObject->setData($object->getData());
                        $copiedObject->setCurrency($object->getCurrency());
                        $copiedData->add($copiedObject);
                    } elseif ($object instanceof AttributeOption) {
                        $copiedData->add($object);
                    }
                }
            }

        } elseif (is_object($originalData)) {
            $copiedData = $this->publisher->publish($originalData);

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
}
